<?php

namespace App\Support;

/**
 * File-only logging. Avoids Laravel's Log/Monolog facade entirely because a
 * locked or broken storage/logs/laravel.log can hang/kill requests & cron jobs.
 */
class SafeLog
{
    public static function info(string $message, array $context = []): void
    {
        self::write('info', $message, $context);
    }

    public static function warning(string $message, array $context = []): void
    {
        self::write('warning', $message, $context);
    }

    public static function error(string $message, array $context = []): void
    {
        self::write('error', $message, $context);
    }

    public static function write(string $level, string $message, array $context = []): void
    {
        self::file('debug-runtime.log', strtoupper($level).' '.$message, $context);

        if (str_contains(strtolower($message), 'nickname')
            || str_contains(strtolower($message), 'provider nickname')) {
            self::file('nickname-debug.log', strtoupper($level).' '.$message, $context);
        }

        if (str_contains(strtolower($message), 'digiflazz')) {
            self::file('digiflazz-sync.log', strtoupper($level).' '.$message, $context);
        }
    }

    public static function file(string $filename, string $message, array $context = []): void
    {
        try {
            $dir = storage_path('logs');
            if (! is_dir($dir)) {
                @mkdir($dir, 0775, true);
            }

            $line = '['.date('Y-m-d H:i:s').'] '.$message;
            if (! empty($context)) {
                $encoded = json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                if ($encoded !== false) {
                    $line .= ' '.$encoded;
                }
            }
            $line .= PHP_EOL;

            @file_put_contents($dir.DIRECTORY_SEPARATOR.$filename, $line, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            @error_log('SafeLog file failed: '.$e->getMessage().' | '.$message);
        }
    }
}
