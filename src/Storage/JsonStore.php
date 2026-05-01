<?php

declare(strict_types=1);

namespace App\Storage;

use RuntimeException;

/**
 * Tiny JSON-on-disk store.
 *
 * Reads are cached for the lifetime of the request. Writes are atomic
 * (write to a temp file, then rename) and guarded with an exclusive flock.
 */
final class JsonStore
{
    /** @var array<string, array<int|string, mixed>> */
    private array $cache = [];

    public function __construct(
        private readonly string $dataDir,
        private readonly string $runtimeDir,
    ) {
    }

    /**
     * @return array<int|string, mixed>
     */
    public function read(string $name): array
    {
        if (isset($this->cache[$name])) {
            return $this->cache[$name];
        }

        $path = $this->resolvePath($name);
        if (!is_file($path)) {
            throw new RuntimeException(sprintf('Data file not found: %s', $path));
        }

        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new RuntimeException(sprintf('Cannot read data file: %s', $path));
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            throw new RuntimeException(sprintf('Invalid JSON in %s: %s', $path, $e->getMessage()), 0, $e);
        }

        if (!is_array($decoded)) {
            throw new RuntimeException(sprintf('Expected JSON array in %s', $path));
        }

        return $this->cache[$name] = $decoded;
    }

    /**
     * @return array<int|string, mixed>
     */
    public function readOrEmpty(string $name): array
    {
        $path = $this->resolvePath($name);
        if (!is_file($path)) {
            return [];
        }

        return $this->read($name);
    }

    /**
     * @param array<int|string, mixed> $data
     */
    public function write(string $name, array $data): void
    {
        $path = $this->resolveRuntimePath($name);
        $dir = dirname($path);
        if (!is_dir($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
            throw new RuntimeException(sprintf('Cannot create runtime dir: %s', $dir));
        }

        $tmp = $path . '.' . bin2hex(random_bytes(4)) . '.tmp';
        $payload = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES,
        );
        if ($payload === false) {
            throw new RuntimeException('JSON encode failed');
        }

        $fp = fopen($tmp, 'wb');
        if ($fp === false) {
            throw new RuntimeException(sprintf('Cannot open temp file: %s', $tmp));
        }

        try {
            if (!flock($fp, LOCK_EX)) {
                throw new RuntimeException('Cannot acquire write lock');
            }
            fwrite($fp, $payload);
            fflush($fp);
            flock($fp, LOCK_UN);
        } finally {
            fclose($fp);
        }

        if (!rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException(sprintf('Cannot rename %s to %s', $tmp, $path));
        }

        $this->cache[$name] = $data;
    }

    private function resolvePath(string $name): string
    {
        $runtime = $this->runtimeDir . DIRECTORY_SEPARATOR . $name . '.json';
        if (is_file($runtime)) {
            return $runtime;
        }

        return $this->dataDir . DIRECTORY_SEPARATOR . $name . '.json';
    }

    private function resolveRuntimePath(string $name): string
    {
        return $this->runtimeDir . DIRECTORY_SEPARATOR . $name . '.json';
    }
}
