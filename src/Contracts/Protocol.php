<?php

declare(strict_types=1);

namespace Al\Client\Contracts;

interface Protocol
{
    public function containsOne(string $buffer, string $headFormat, int $headOffset): array|bool;

    public function pack(string $headFormat, string $payload): string;

    public function unpack(string $headFormat, string $bin): int;
}