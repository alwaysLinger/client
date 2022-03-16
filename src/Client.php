<?php

declare(strict_types=1);

namespace Al\Client;

use Al\Client\Contracts\Event;
use Al\Client\Contracts\Protocol;
use Al\Client\Protocols\Stream;
use Al\Client\Wrappers\StreamWrapper;
use Al\Client\Wrappers\TextWrapper;
use Al\Client\Wrappers\Wrapper;
use Al\Client\Events\Epoll;
use Exception;

class Client
{
    protected array $clientMeta;
    protected Wrapper $wrapper;
    protected array $callbacks = [];
    protected Event $event;
    protected Protocol $protocl;
    protected $resource = null;
    protected Buffer $buffer;

    /**
     * @throws Exception
     */
    public function __construct(
        protected string $addr,
        protected array  $context = [],
        protected array  $wrappers = [
            StreamWrapper::WRAPPER_NAME => StreamWrapper::class,
            TextWrapper::WRAPPER_NAME => TextWrapper::class,
        ]
    )
    {
        $this->clientMeta = $this->parseUrl($this->addr);
        $this->wrapper = $this->genWrapper($this->clientMeta['scheme']);
        $this->registerStreamWrappers();
    }

    private function parseUrl(string $addr): array
    {
        $addrs = parse_url($addr);
        $addrs['scheme'] ??= 'stream';
        $addrs['host'] ??= '127.0.0.1';
        $addrs['port'] ??= 9527;

        return $addrs;
    }

    /**
     * @param string $addr
     * @return Wrapper
     * @throws Exception
     */
    private function genWrapper(string $addr): Wrapper
    {
        return match ($addr) {
            StreamWrapper::WRAPPER_NAME => new StreamWrapper(),
            TextWrapper::WRAPPER_NAME => new TextWrapper(),
        };
    }

    private function registerStreamWrappers(): void
    {
        array_walk($this->wrappers, fn($class, $wrapper) => stream_register_wrapper($wrapper, $class));
    }

    /**
     * @throws Exception
     */
    private function connect(): void
    {
        $url = sprintf('%s://%s:%d', $this->clientMeta['scheme'], $this->clientMeta['host'], $this->clientMeta['port']);
        $context = $this->context;
        $context['event'] ??= Epoll::class;
        $context['protocl'] ??= Stream::class;
        $context['wrapper'] ??= $this->clientMeta['scheme'];
        $context['head_format'] ??= 'N';
        $context['head_offset'] ??= 4;
        $this->buffer = $context['buffer'] = new Buffer(
            event: $this->event = new $context['event'](),
            protocol: $this->protocl = new $context['protocl'](),
            wrapper: $context['wrapper'],
            headFormat: $context['head_format'],
            headOffset: $context['head_offset'],
            client: $this,
        );
        $ctx = stream_context_create(['user' => $context]);
        $this->resource = $resource = fopen(filename: $url, mode: 'w+', context: $ctx);
        if (!is_resource($resource)) {
            throw new Exception(sprintf('failed to wrapper resource %s to a resource', $url));
        }
        $this->hook('connect');
    }

    public function send(string $payload): bool
    {
        $this->buffer->appendWriteBuffer($payload);
        return true;
    }

    public function onConnect(callable $callback): void
    {
        $this->on('connect', $callback);
    }

    public function onReceive(callable $callback): void
    {
        $this->on('receive', $callback);
    }

    public function getCallbacks(): array
    {
        return $this->callbacks;
    }

    public function close(): bool
    {
        if (is_null($this->resource)) {
            return false;
        }
        $this->event->del($this->resource);
        @fclose($this->resource);

        return true;
    }

    private function on(string $event, callable $callable): void
    {
        $this->callbacks[$event] = $this->forwardsTo($callable);
    }

    private function forwardsTo(callable $callable): callable
    {
        return fn(...$args) => call_user_func($callable, ...$args);
    }

    private function hook(string $event): void
    {
        if (!isset($this->callbacks[$event])) {
            return;
        }
        call_user_func($this->callbacks[$event], ...[$this]);
    }

    /**
     * @throws Exception
     */
    public function start(): void
    {
        if (empty($this->getCallbacks())) {
            throw new Exception('no registered callback found');
        }
        $this->connect();
        $this->event->loop();
    }
}