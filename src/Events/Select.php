<?php

namespace Al\Client\Events;

use Al\Client\Contracts\Event;

class Select implements Event
{
    public function __construct(
        protected array $writes = [],
        protected array $reads = [],
        protected array $excepts = [],
        protected array $onWrite = [],
        protected array $onRead = [],
        protected array $args = [],
        protected bool  $running = true,
    )
    {
    }

    public function add($fd, int $eventType, callable $cb, array $args = []): bool
    {
        $this->args = $args;
        if ($eventType == Event::READ) {
            $this->reads[get_resource_id($fd)] = $fd;
            $this->onRead = $cb;
        } else {
            $this->writes[get_resource_id($fd)] = $fd;
            $this->onWrite = $cb;
        }
        return true;
    }

    public function del(): bool
    {
        return true;
    }

    public function loop(): void
    {
        while ($this->running) {
            $reads = $this->reads;
            $writes = $this->writes;
            $excepts = $this->excepts;

            stream_select($reads, $writes, $excepts, null, null);
            if ($reads) {
                call_user_func_array($this->onRead, $this->args);
            }
            if ($writes) {
                call_user_func_array($this->onWrite, $this->args);
            }
        }
    }

    public function exitLoop(): void
    {
        $this->running = false;
    }
}