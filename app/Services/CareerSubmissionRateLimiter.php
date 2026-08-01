<?php

namespace App\Services;

use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CareerSubmissionRateLimiter
{
    public const SESSION_MAX_ATTEMPTS = 3;

    public const IP_MAX_ATTEMPTS = 10;

    public const DECAY_SECONDS = 600;

    /**
     * Lakukan reservasi attempt secara atomik menggunakan Cache::lock.
     * Mengembalikan objek/array reservasi jika berhasil, atau null jika rate limit terlampaui.
     */
    public function reserve(string $formType, int $carrerId, string $ip, string $sessionId): ?array
    {
        $sessionKey = $this->buildSessionKey($formType, $carrerId, $ip, $sessionId);
        $ipKey = $this->buildIpKey($formType, $carrerId, $ip);

        $lockKey = 'career_rate_limit_lock:'.hash('sha256', "{$formType}:{$carrerId}:{$ip}:{$sessionId}");
        $lock = Cache::lock($lockKey, 5);

        try {
            return $lock->block(3, function () use ($sessionKey, $ipKey, $formType, $carrerId) {
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

                return [
                    'sessionKey' => $sessionKey,
                    'ipKey' => $ipKey,
                    'formType' => $formType,
                    'carrerId' => $carrerId,
                ];
            });
        } catch (LockTimeoutException $e) {
            Log::warning('RateLimiter Cache::lock timeout.', ['form' => $formType, 'carrer_id' => $carrerId]);

            return null;
        }
    }

    /**
     * Rollback reservasi attempt jika proses upload atau transaksi database gagal.
     * Hanya meng-decrement key milik request tersebut dan memastikan nilai tidak pernah negatif.
     */
    public function rollback(?array $reservation): void
    {
        if (empty($reservation) || ! isset($reservation['sessionKey'], $reservation['ipKey'])) {
            return;
        }

        $sessionKey = $reservation['sessionKey'];
        $ipKey = $reservation['ipKey'];

        // Decrement session counter safely
        if (Cache::has($sessionKey)) {
            $current = (int) Cache::get($sessionKey, 0);
            if ($current > 1) {
                Cache::decrement($sessionKey);
            } else {
                Cache::forget($sessionKey);
            }
        }

        // Decrement IP counter safely
        if (Cache::has($ipKey)) {
            $current = (int) Cache::get($ipKey, 0);
            if ($current > 1) {
                Cache::decrement($ipKey);
            } else {
                Cache::forget($ipKey);
            }
        }
    }

    /**
     * Hitung sisa detik waktu tunggu jika rate limit terlampaui.
     */
    public function availableIn(string $formType, int $carrerId, string $ip, string $sessionId): int
    {
        return static::DECAY_SECONDS;
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
}
