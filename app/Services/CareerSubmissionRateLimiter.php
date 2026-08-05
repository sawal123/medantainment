<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class CareerSubmissionRateLimiter
{
    public const SESSION_MAX_ATTEMPTS = 3;

    public const IP_MAX_ATTEMPTS = 10;

    public const DECAY_SECONDS = 600;

    public const LOCK_SECONDS = 5;

    public const LOCK_WAIT_SECONDS = 3;

    /**
     * Lakukan reservasi attempt secara atomik menggunakan Cache::lock berbasis IP bucket.
     * Mengembalikan array reservasi jika berhasil, atau null jika rate limit terlampaui.
     */
    public function reserve(string $formType, int $carrerId, string $ip, string $sessionId): ?array
    {
        $sessionKey = $this->buildSessionKey($formType, $carrerId, $ip, $sessionId);
        $ipKey = $this->buildIpKey($formType, $carrerId, $ip);
        $lockKey = $this->buildLockKey($formType, $carrerId, $ip);

        $lock = Cache::lock($lockKey, static::LOCK_SECONDS);

        try {
            return $lock->block(static::LOCK_WAIT_SECONDS, function () use ($sessionKey, $ipKey, $lockKey) {
                $sessionAttempts = (int) Cache::get($sessionKey, 0);
                $ipAttempts = (int) Cache::get($ipKey, 0);

                if ($sessionAttempts >= static::SESSION_MAX_ATTEMPTS || $ipAttempts >= static::IP_MAX_ATTEMPTS) {
                    return null;
                }

                // Increment session bucket
                if (Cache::has($sessionKey)) {
                    Cache::increment($sessionKey);
                } else {
                    Cache::put($sessionKey, 1, static::DECAY_SECONDS);
                }

                // Increment IP bucket
                if (Cache::has($ipKey)) {
                    Cache::increment($ipKey);
                } else {
                    Cache::put($ipKey, 1, static::DECAY_SECONDS);
                }

                $token = (string) Str::uuid();
                $reservationKey = $this->buildReservationKey($token);

                Cache::put(
                    $reservationKey,
                    [
                        'session_key' => $sessionKey,
                        'ip_key' => $ipKey,
                    ],
                    static::DECAY_SECONDS
                );

                return [
                    'token' => $token,
                    'lock_key' => $lockKey,
                    'reservation_key' => $reservationKey,
                ];
            });
        } catch (LockTimeoutException $e) {
            Log::warning('RateLimiter Cache::lock timeout.', [
                'form' => $formType,
                'carrer_id' => $carrerId,
                'lock_key' => $lockKey,
            ]);

            return null;
        }
    }

    /**
     * Rollback reservasi attempt jika proses upload atau transaksi database gagal.
     * Menggunakan lock IP bucket yang sama untuk mencegah race condition.
     */
    public function rollback(?array $reservation): void
    {
        if (! $this->isValidReservation($reservation)) {
            return;
        }

        $lock = Cache::lock($reservation['lock_key'], static::LOCK_SECONDS);

        try {
            $lock->block(static::LOCK_WAIT_SECONDS, function () use ($reservation) {
                $reservationKey = $this->buildReservationKey($reservation['token']);
                $payload = Cache::pull($reservationKey);

                if (! is_array($payload) || ! isset($payload['session_key'], $payload['ip_key'])) {
                    return;
                }

                $this->safeDecrement($payload['session_key']);
                $this->safeDecrement($payload['ip_key']);
            });
        } catch (LockTimeoutException $e) {
            Log::warning('RateLimiter rollback lock timeout.', ['lock_key' => $reservation['lock_key'] ?? 'unknown']);
        }
    }

    /**
     * Konfirmasi reservasi setelah submission berhasil.
     * Hanya menghapus marker reservasi tanpa mengurangi counter attempt.
     */
    public function commit(?array $reservation): void
    {
        if (! $this->isValidReservation($reservation)) {
            return;
        }

        $reservationKey = $this->buildReservationKey($reservation['token']);
        Cache::forget($reservationKey);
    }

    /**
     * Hitung perkiraan sisa detik waktu tunggu jika rate limit terlampaui.
     * Menggunakan DECAY_SECONDS sebagai fallback standar yang konsisten dan didaftarkan sebagai estimasi ("sekitar").
     */
    public function availableIn(string $formType, int $carrerId, string $ip, string $sessionId): int
    {
        return static::DECAY_SECONDS;
    }

    public function buildLockKey(string $formType, int $carrerId, string $ip): string
    {
        return 'career-rate-limit-lock:'.hash('sha256', implode('|', [
            $formType,
            $carrerId,
            $ip,
        ]));
    }

    public function buildSessionKey(string $formType, int $carrerId, string $ip, string $sessionId): string
    {
        $hash = hash('sha256', "{$formType}|{$carrerId}|{$ip}|{$sessionId}");

        return "rl:session:{$formType}:{$carrerId}:{$hash}";
    }

    public function buildIpKey(string $formType, int $carrerId, string $ip): string
    {
        $hash = hash('sha256', "{$formType}|{$carrerId}|{$ip}");

        return "rl:ip:{$formType}:{$carrerId}:{$hash}";
    }

    public function buildReservationKey(string $uuid): string
    {
        return "rl:career:reservation:{$uuid}";
    }

    private function isValidReservation(?array $reservation): bool
    {
        return is_array($reservation) && ! empty($reservation['token']) && ! empty($reservation['lock_key']);
    }

    private function safeDecrement(string $key): void
    {
        if (Cache::has($key)) {
            $current = (int) Cache::get($key, 0);
            if ($current > 1) {
                Cache::decrement($key);
            } else {
                Cache::forget($key);
            }
        }
    }
}
