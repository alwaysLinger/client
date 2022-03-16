<?php

declare(strict_types=1);

namespace Al\Client\Protocols;

use Al\Client\Contracts\Protocol;

class Stream implements Protocol
{
    public function containsOne(string $buffer, string $headFormat, int $headOffset): array|bool
    {
        if (strlen($buffer) <= $headOffset) {
            return false;
        }
        $payloadLength = $this->unpack($headFormat, substr($buffer, 0, $headOffset));
        if (strlen($buffer) < $headOffset + $payloadLength) {
            return false;
        }

        return [substr($buffer, $headOffset, $payloadLength), substr($buffer, $headOffset + $payloadLength)];
    }

    public function pack(string $headFormat, string $payload): string
    {
        return pack($headFormat, strlen($payload)) . $payload;
    }

    public function unpack(string $headFormat, string $bin): int
    {
        return unpack($headFormat, $bin)[1];
    }
}