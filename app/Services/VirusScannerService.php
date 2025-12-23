<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

/**
 * Simple Virus/Malware scanner wrapper using ClamAV tools.
 *
 * It prefers clamdscan (daemon) then falls back to clamscan.
 * Returns an array: ['clean' => bool, 'message' => string]
 */
class VirusScannerService
{
    // Use environment variable to opt in/out
    protected bool $enabled;

    public function __construct()
    {
        $this->enabled = (bool) config('services.virus_scan.enabled', env('VIRUS_SCAN_ENABLED', false));
        Log::info('VirusScannerService initialized', ['enabled' => $this->enabled]);
    }

    /**
     * Scan a local file path.
     * @param string $filePath
     * @return array ['clean' => bool, 'message' => string]
     */
    public function scanFile(string $filePath): array
    {
        if (!$this->enabled) {
            return ['clean' => true, 'message' => 'Scanning disabled'];
        }

        if (!file_exists($filePath)) {
            return ['clean' => false, 'message' => 'File not found for scanning'];
        }

        $candidates = [
            ['cmd' => ['clamdscan', '--no-summary', $filePath], 'name' => 'clamdscan'],
            ['cmd' => ['clamscan', '--no-summary', $filePath], 'name' => 'clamscan'],
        ];

        foreach ($candidates as $c) {
            try {
                $process = new Process($c['cmd']);
                $process->setTimeout(30);
                $process->run();

                // Exit codes: 0 = OK, 1 = infected, >1 = error
                $exit = $process->getExitCode();
                $output = trim($process->getOutput() . ' ' . $process->getErrorOutput());

                if ($exit === 0) {
                    return ['clean' => true, 'message' => "Scanned with {$c['name']} - clean"]; 
                }

                if ($exit === 1) {
                    // infected
                    return ['clean' => false, 'message' => "Infected: {$output}"];
                }

                // error running scanner — try next
                Log::warning('Virus scanner returned error code', ['exe' => $c['name'], 'exit' => $exit, 'output' => $output]);
            } catch (\Throwable $e) {
                Log::error('Virus scanner execution failed', ['exe' => $c['name'], 'error' => $e->getMessage()]);
            }
        }

        // If none of the scanners are available or all failed, log and treat as suspicious
        Log::warning('No virus scanner available or all scanners failed for file', ['file' => $filePath]);
        return ['clean' => false, 'message' => 'Virus scanner not available or returned an error'];
    }
}
