<?php

declare(strict_types=1);

namespace Al\Client\Events;

use Al\Client\Contracts\Event;
use EventBase;
use Event as PhpEvent;

class Epoll implements Event
{
    private EventBase $eb;
    private array $fds = [];

    public function __construct()
    {
        $this->eb = new EventBase();
    }

    public function add($fd, int $eventType, callable $cb, array $args = []): bool
    {
        $flags = match ($eventType) {
            Event::READ => PhpEvent::READ | PhpEvent::PERSIST,
            Event::WRITE => PhpEvent::WRITE | PhpEvent::PERSIST,
        };
        $evt = new PhpEvent($this->eb, $fd, $flags, $cb, $args);
        if ($evt->add()) {
            $this->fds[get_resource_id($fd)][$eventType] = $evt;
            return true;
        }
        return false;
    }

    public function del($fd): bool
    {
        $rret = ($this->fds[get_resource_id($fd)][Event::WRITE] ?? null)?->del();
        $wret = ($this->fds[get_resource_id($fd)][Event::READ] ?? null)?->del();
        unset($this->fds[$fd]);
        return $rret && $wret;
    }

    public function loop(): void
    {
        $this->eb->loop();
    }

    public function exitLoop(): void
    {
        $this->eb->stop();
    }
}