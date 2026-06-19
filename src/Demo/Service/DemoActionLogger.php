<?php

declare(strict_types=1);

namespace App\Demo\Service;

use DateTimeImmutable;
use RuntimeException;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * T-082d JSONL-Action-Log für Demo-CLI.
 *
 * - Append-only via fopen 'a'.
 * - Kein Auto-Rotate (Decision: Demo wächst überschaubar; Reset macht .bak).
 * - Reset-Hook: bei `--reset` wird existierendes Log nach
 *   `var/demo-log-{Ymd-His}.jsonl.bak` umbenannt damit Vorgeschichte erhalten bleibt.
 */
class DemoActionLogger
{
    private string $logPath;

    public function __construct(
        #[Autowire('%kernel.project_dir%')]
        string $projectDir,
    ) {
        $varDir = $projectDir . '/var';
        if (!is_dir($varDir)) {
            mkdir($varDir, 0775, true);
        }
        $this->logPath = $varDir . '/demo-log.jsonl';
    }

    public function getLogPath(): string
    {
        return $this->logPath;
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $snapshot
     */
    public function log(string $action, array $params, array $snapshot, bool $success = true, ?string $error = null): void
    {
        $entry = [
            'ts' => (new DateTimeImmutable())->format('Y-m-d\TH:i:s.uP'),
            'action' => $action,
            'params' => $params,
            'success' => $success,
        ];
        if ($error !== null) {
            $entry['error'] = $error;
        }
        $entry['snapshot'] = $snapshot;

        $line = json_encode($entry, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        if ($line === false) {
            throw new RuntimeException('Failed to encode log entry to JSON');
        }
        file_put_contents($this->logPath, $line . "\n", FILE_APPEND | LOCK_EX);
    }

    /**
     * Bei --reset aufgerufen: existierendes Log nach .bak verschieben + Pfad zurückgeben.
     * Returns null wenn kein Log existierte.
     */
    public function backupOnReset(): ?string
    {
        if (!file_exists($this->logPath)) {
            return null;
        }
        $stamp = (new DateTimeImmutable())->format('Ymd-His');
        $backup = dirname($this->logPath) . sprintf('/demo-log-%s.jsonl.bak', $stamp);
        if (!rename($this->logPath, $backup)) {
            throw new RuntimeException(sprintf('Failed to backup log to %s', $backup));
        }

        return $backup;
    }

    /**
     * Liefert die letzten N JSON-Zeilen als parsed-Array. Liefert leeres Array wenn
     * Log nicht existiert.
     *
     * @return list<array<string, mixed>>
     */
    public function readLast(int $n): array
    {
        if (!file_exists($this->logPath)) {
            return [];
        }
        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) {
            return [];
        }
        $tail = array_slice($lines, -$n);
        $parsed = [];
        foreach ($tail as $line) {
            $obj = json_decode($line, true);
            if (is_array($obj)) {
                $parsed[] = $obj;
            }
        }

        return $parsed;
    }

    public function lineCount(): int
    {
        if (!file_exists($this->logPath)) {
            return 0;
        }
        $lines = file($this->logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return $lines === false ? 0 : count($lines);
    }
}
