<?php

declare(strict_types=1);

namespace Al\Client;

use Al\Client\Attributes\ClientMeta;
use Al\Client\Wrappers\Wrapper;
use Al\Client\Wrappers\TcpWrapper;
use Al\Events\Epoll;
use Al\Client\Protocols\Stream;

// 使用注解确定wrapper
// client是一个代理类
#[ClientMeta()]
class Client
{
    public function __construct(
        protected string  $addr,
        protected array   $context = [],
        protected Wrapper $wrapper,
    )
    {

    }

    public function onConnect(): void
    {

    }

    public function onReceive(): void
    {

    }

    public function close(): bool
    {
        return true;
    }

    public function start(): void
    {

    }
}