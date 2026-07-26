<?php

namespace App\Support;

/**
 * Logging that never takes down a request/command if storage/logs is broken.
 */
class SafeLog
{
    public static function write(string $level, string $message, array $context = []): void
    {
        try {
            $logger = \Illuminate\Support\Facades\Log::getFacadeRoot();
            if ($logger && method_exists($logger, $level)) {
                $logger->{$level}($message, $context);
            }
        } catch (\Throwable $e) {
            self::file('laravel-fallback.log', 'LOG_FAIL '.$e->getMessage().' | '.$message, $context);
        }

        // Always mirror critical debug lines to a dedicated file
        if (in_array($level, ['error', 'warning', 'critical'], true)
            || str_contains($message, 'nickname')
            || str_contains($message, 'Digiflazz sync')
            || str_contains($message, 'Provider nickname')) {
            self::file('debug-runtime.log', strtoupper($level).' '.$message, $context);
        }
    }

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
