<?php

declare(strict_types=1);

namespace Al\Client\Attributes;

use Al\Client\Contracts\Protocol;
use Attribute;

#[Attribute]
class WrapperMeta
{
    public function __construct(
        private string   $headFormat,
        private int      $headOffset,
        private string $protocol,
    )
    {

    }

    /**
     * @return string
     */
    public function getHeadFormat(): string
    {
        return $this->headFormat;
    }

    /**
     * @return int
     */
    public function getHeadOffset(): int
    {
        return $this->headOffset;
    }

    /**
     * @return Protocol
     */
    public function getProtocol(): Protocol
    {
        return $this->protocol;
    }
}