<?php

declare(strict_types=1);

namespace Al\Client\Wrappers;

use Al\Client\Attributes\WrapperMeta;
use Al\Client\Contracts\Protocol;

class Wrapper
{
    public function __construct(
        public string   $headFormat,
        public int      $headOffset,
        public Protocol $protocol,
    )
    {

    }

    public function stream_open()
    {

    }

    public function stream_read()
    {

    }

    public function stream_eof()
    {

    }

    public function stream_close()
    {

    }
}