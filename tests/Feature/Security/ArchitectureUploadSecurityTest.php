<?php

namespace Tests\Feature\Security;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;
use Tests\TestCase;

class ArchitectureUploadSecurityTest extends TestCase
{
    public function test_no_use_of_unsafe_client_original_extension_or_name_in_app_directory(): void
    {
        $appPath = app_path();
        $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($appPath));
        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        $forbiddenMethods = [
            'getClientOriginalExtension',
            'getClientOriginalName',
        ];

        $violations = [];

        foreach ($phpFiles as $file) {
            $filePath = $file[0];
            $content = file_get_contents($filePath);

            foreach ($forbiddenMethods as $method) {
                if (str_contains($content, $method)) {
                    $violations[] = "File [{$filePath}] memuat panggilan tidak aman: {$method}()";
                }
            }
        }

        $this->assertEmpty(
            $violations,
            "Ditemukan penggunaan ekstensi/nama asli dari client di folder app:\n".implode("\n", $violations)
        );
    }
}
