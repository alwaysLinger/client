<?php

declare(strict_types=1);

namespace Al\Client\Contracts;

interface Protocol
{
    public function containsOne(): bool;

    public function pack();

    public function unpack();
}