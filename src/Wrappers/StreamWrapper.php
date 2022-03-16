<?php

declare(strict_types=1);

namespace Al\Client\Wrappers;

use Al\Client\Buffer;
use Al\Client\Contracts\Event;
use Exception;

class StreamWrapper implements Wrapper
{
    protected array $ctx = [];
    const WRAPPER_NAME = 'stream';

    protected Buffer $buffer;
    protected Event $event;

    /**
     * @param string $path
     * @param string $mode
     * @param int $options
     * @param string|null $open
     * @return bool
     * @throws Exception
     */
    public function stream_open(string $path, string $mode, int $options, ?string $open): bool
    {
        $this->ctx = stream_context_get_options($this->context);
        $path = str_replace($this->ctx['user']['wrapper'], 'tcp', $path);
        $res = @stream_socket_client(address: $path, error_code: $errCode, error_message: $errMsg, timeout: 10);
        if (!$res) {
            throw new Exception(message: sprintf('connect to %s faield,code:%d, msg:%s', $path, $errCode, $errMsg), code: $errCode);
        }
        stream_set_blocking($res, false);
        stream_set_read_buffer($res, 0);
        stream_set_write_buffer($res, 0);
        $this->buffer = $this->ctx['user']['buffer'];
        $this->event = $this->buffer->getEvent();
        $this->event->add($res, Event::READ, [$this->buffer, 'appendBuffer'], [$res, $this]);
        $this->event->add($res, Event::WRITE, [$this->buffer, 'consumeBuffer'], [$res, $this]);
        $this->resource = $res;
        return true;
    }

    public function stream_read(): string|bool
    {
        if ($this->isClose()) {
            return false;
        }
        return $this->buffer->getOne();
    }

    public function stream_eof(): bool
    {
        return $this->isClose();
    }

    public function stream_close(): void
    {
        if ($this->isClose()) {
            $this->event->exitLoop();
            return;
        }
        $this->event->del($this->resource);
        @fclose($this->resource);
        $this->event->exitLoop();
    }

    public function stream_write(string $data): int
    {
        if ($this->isClose()) {
            $this->event->del($this->resource);
            $this->event->exitLoop();
            return 0;
        }
        return @fwrite($this->resource, $data);
    }

    private function isClose(): bool
    {
        return !is_resource($this->resource) || feof($this->resource) || strlen($this->buffer->getLastRecBuff()) == 0;
    }
}