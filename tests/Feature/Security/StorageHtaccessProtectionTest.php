<?php

namespace Tests\Feature\Security;

use Tests\TestCase;

class StorageHtaccessProtectionTest extends TestCase
{
    public function test_storage_app_public_htaccess_file_exists_and_is_tracked(): void
    {
        $htaccessPath = storage_path('app/public/.htaccess');
        $gitignorePath = storage_path('app/public/.gitignore');

        $this->assertFileExists($htaccessPath, 'File storage/app/public/.htaccess wajib ada.');
        $this->assertFileExists($gitignorePath, 'File storage/app/public/.gitignore wajib ada.');

        $gitignoreContent = file_get_contents($gitignorePath);
        $this->assertStringContainsString('!.htaccess', $gitignoreContent, '.gitignore harus mengecualikan .htaccess agar ter-track git.');
    }

    public function test_public_root_htaccess_blocks_script_requests_in_storage(): void
    {
        $publicHtaccessPath = public_path('.htaccess');
        $this->assertFileExists($publicHtaccessPath);

        $content = file_get_contents($publicHtaccessPath);
        $this->assertStringContainsString('/storage/', $content);
        $this->assertStringContainsString('RewriteCond %{REQUEST_URI}', $content);
    }

    public function test_storage_htaccess_blocks_all_script_extensions(): void
    {
        $htaccessPath = storage_path('app/public/.htaccess');
        $content = file_get_contents($htaccessPath);

        $blockedExtensions = ['php', 'php3', 'php4', 'php5', 'php7', 'php8', 'phtml', 'phar', 'phps'];

        foreach ($blockedExtensions as $ext) {
            $this->assertStringContainsString($ext, $content, "Ekstensi [{$ext}] wajib diblokir di storage/app/public/.htaccess.");
        }
    }
}
