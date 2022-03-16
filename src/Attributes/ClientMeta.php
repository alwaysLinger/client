<?php

declare(strict_types=1);

namespace Al\Client\Attributes;

use Al\Client\Contracts\Event;
use Al\Client\Contracts\Protocol;
use Al\Client\Protocols\Stream;
use Al\Client\Wrappers\TcpWrapper;
use Al\Client\Wrappers\Wrapper;
use Al\Events\Epoll;
use Attribute;

#[Attribute]
class ClientMeta
{
    protected ?Wrapper $_wrapper;
    protected ?Event $_event;
    protected ?Protocol $_protocol;

    public function __construct(
        protected string $wrapper,
        protected string $event,
        protected string $protocol,
        protected string $client,
    )
    {
    }

    public function getProtocol(): Protocol|bool
    {
        if ($this->_protocol instanceof Protocol) {
            return $this->_protocol;
        }

        $proto = new $this->protocol();
        if ($proto instanceof Protocol) {
            return $this->_protocol = $proto;
        }

        return false;
    }


    public function getEvent(): Event|bool
    {
        if ($this->_event instanceof Event) {
            return $this->_event;
        }

        $event = new $this->event();
        if ($event instanceof Event) {
            return $this->_event = $event;
        }

        return false;
    }


    public function getWrapper(): Wrapper|bool
    {
        if (is_subclass_of($this->_wrapper, Wrapper::class)) {
            return $this->_wrapper;
        }

        $wrapper = new $this->wrapper();
        if (is_subclass_of($wrapper, Wrapper::class)) {
            return $this->_wrapper = $wrapper;
        }

        return false;
    }
}