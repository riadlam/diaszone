<?php

namespace App\Services;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;

class SitePauseService
{
    public const FLAG_PATH = 'framework/site-paused';

    public function isPaused(): bool
    {
        return File::exists($this->flagFile());
    }

    /**
     * @return array{paused_at: string|null, paused_by: string|null, message: string|null}
     */
    public function status(): array
    {
        if (! $this->isPaused()) {
            return [
                'paused_at' => null,
                'paused_by' => null,
                'message' => null,
            ];
        }

        $raw = File::get($this->flagFile());
        $data = json_decode($raw, true);

        if (! is_array($data)) {
            return [
                'paused_at' => null,
                'paused_by' => null,
                'message' => null,
            ];
        }

        return [
            'paused_at' => $data['paused_at'] ?? null,
            'paused_by' => $data['paused_by'] ?? null,
            'message' => $data['message'] ?? null,
        ];
    }

    public function pause(?string $message = null): void
    {
        File::ensureDirectoryExists(dirname($this->flagFile()));

        File::put($this->flagFile(), json_encode([
            'paused_at' => now()->toIso8601String(),
            'paused_by' => Auth::user()?->email,
            'message' => $message,
        ], JSON_PRETTY_PRINT));
    }

    public function resume(): void
    {
        if (File::exists($this->flagFile())) {
            File::delete($this->flagFile());
        }
    }

    private function flagFile(): string
    {
        return storage_path(self::FLAG_PATH);
    }
}
