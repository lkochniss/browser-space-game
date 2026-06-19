<?php

declare(strict_types=1);

namespace App\Tests\Demo\Service;

use App\Demo\Service\DemoActionLogger;
use PHPUnit\Framework\TestCase;

final class DemoActionLoggerTest extends TestCase
{
    private string $tmpDir;
    private DemoActionLogger $logger;

    protected function setUp(): void
    {
        $this->tmpDir = sys_get_temp_dir() . '/demo-log-test-' . uniqid();
        mkdir($this->tmpDir);
        $this->logger = new DemoActionLogger($this->tmpDir);
    }

    protected function tearDown(): void
    {
        $this->cleanupDir($this->tmpDir);
    }

    public function test_log_path_is_jsonl_in_var(): void
    {
        self::assertStringEndsWith('/var/demo-log.jsonl', $this->logger->getLogPath());
    }

    public function test_logging_appends_jsonl(): void
    {
        $this->logger->log('Status', [], ['clock_now' => 'now']);
        $this->logger->log('BuildBuilding', ['type' => 'iron_mine'], ['clock_now' => 'now+1']);

        self::assertSame(2, $this->logger->lineCount());

        $entries = $this->logger->readLast(10);
        self::assertCount(2, $entries);
        self::assertSame('Status', $entries[0]['action']);
        self::assertSame('BuildBuilding', $entries[1]['action']);
        self::assertSame('iron_mine', $entries[1]['params']['type']);
    }

    public function test_logged_entry_has_required_fields(): void
    {
        $this->logger->log('TestAction', ['k' => 'v'], ['clock_now' => '2026-06-19']);

        $entries = $this->logger->readLast(1);
        self::assertCount(1, $entries);

        $e = $entries[0];
        self::assertArrayHasKey('ts', $e);
        self::assertArrayHasKey('action', $e);
        self::assertArrayHasKey('params', $e);
        self::assertArrayHasKey('success', $e);
        self::assertArrayHasKey('snapshot', $e);
        self::assertTrue($e['success']);
        self::assertArrayNotHasKey('error', $e);
    }

    public function test_failed_action_logs_error(): void
    {
        $this->logger->log('FailingAction', [], [], success: false, error: 'boom');

        $entry = $this->logger->readLast(1)[0];
        self::assertFalse($entry['success']);
        self::assertSame('boom', $entry['error']);
    }

    public function test_read_last_n_truncates_if_more_lines(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->logger->log('A', ['i' => $i], []);
        }

        $tail = $this->logger->readLast(3);
        self::assertCount(3, $tail);
        self::assertSame(2, $tail[0]['params']['i']);
        self::assertSame(4, $tail[2]['params']['i']);
    }

    public function test_backup_on_reset_renames_file(): void
    {
        $this->logger->log('A', [], []);
        $this->logger->log('B', [], []);

        $backup = $this->logger->backupOnReset();

        self::assertNotNull($backup);
        self::assertFileExists($backup);
        self::assertFileDoesNotExist($this->logger->getLogPath());
        self::assertStringEndsWith('.jsonl.bak', $backup);
    }

    public function test_backup_on_reset_returns_null_when_no_log(): void
    {
        self::assertNull($this->logger->backupOnReset());
    }

    public function test_line_count_zero_when_no_log(): void
    {
        self::assertSame(0, $this->logger->lineCount());
    }

    private function cleanupDir(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $items = scandir($dir);
        if ($items === false) {
            return;
        }
        foreach ($items as $item) {
            if ($item === '.' || $item === '..') {
                continue;
            }
            $path = $dir . '/' . $item;
            if (is_dir($path)) {
                $this->cleanupDir($path);
            } else {
                unlink($path);
            }
        }
        rmdir($dir);
    }
}
