<?php

declare(strict_types=1);

namespace Al\Client\Contracts;

interface Event
{
    const WRITE = 1;
    const READ = 2;

    public function add($fd, int $eventType, callable $cb, array $args = []): bool;

    public function del(): bool;

    public function loop(): void;

    public function exitLoop(): void;
}