<?php

declare(strict_types=1);

namespace Al\Client\Wrappers;

interface Wrapper
{
    public function stream_open(string $path, string $mode, int $options, ?string $open): bool;

    public function stream_read(): string|bool;

    public function stream_eof(): bool;

    public function stream_write(string $data): int;

    public function stream_close(): void;
}