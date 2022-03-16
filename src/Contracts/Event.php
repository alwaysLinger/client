<?php

declare(strict_types=1);

namespace Al\Client\Contracts;

interface Event
{
    public function add(): bool;

    public function del(): bool;

    public function loop(): void;
}