<?php

declare(strict_types=1);

namespace Al\Client;

use Al\Client\Contracts\Event;
use Al\Client\Contracts\Protocol;
use Exception;

class Buffer
{
    public function __construct(
        protected ?Event   $event,
        protected Protocol $protocol,
        protected string   $wrapper,
        protected string   $headFormat,
        protected int      $headOffset,
        private Client     $client,
        private string     $writeBuffer = '',
        private string     $recvBuffer = '',
        private string     $lastRecBuff = '',
        private string     $one = '',
    )
    {
    }

    /**
     * @return string
     */
    public function getWriteBuffer(): string
    {
        return $this->writeBuffer;
    }

    /**
     * @param string $writeBuffer
     */
    public function appendWriteBuffer(string $writeBuffer): void
    {
        $this->writeBuffer .= $this->protocol->pack($this->headFormat, $writeBuffer);
    }

    /**
     * @return string
     */
    public function getRecvBuffer(): string
    {
        return $this->recvBuffer;
    }

    /**
     * @param string $recvBuffer
     */
    public function appendRecvBuffer(string $recvBuffer): void
    {
        $this->recvBuffer .= $recvBuffer;
        if (!$ret = $this->protocol->containsOne($this->getRecvBuffer(), $this->headFormat, $this->headOffset)) {
            return;
        }
        [$payload, $reserved] = $ret;
        $this->setRecvBuffer($reserved);
        $this->hook('receive', ...[$this->client, $payload]);
    }

    /**
     * @param string $writeBuffer
     */
    public function setWriteBuffer(string $writeBuffer): void
    {
        $this->writeBuffer = $writeBuffer;
    }

    /**
     * @param string $recvBuffer
     */
    public function setRecvBuffer(string $recvBuffer): void
    {
        $this->recvBuffer = $recvBuffer;
    }


    /**
     * @return string
     */
    public function getLastRecBuff(): string
    {
        return $this->lastRecBuff;
    }

    /**
     * @param string $lastRecBuff
     */
    public function setLastRecBuff(string $lastRecBuff): void
    {
        $this->lastRecBuff = $lastRecBuff;
    }

    public function appendBuffer(): void
    {
        $args = func_get_args();
        if (!is_resource($args[0])) {
            $this->clearBuffer();
            $this->event->del();
            $this->event->exitLoop();
        }
        $payload = fread($args[0], 1024);
        if ($payload === '' || 0 === strlen((string)$payload) || $payload === false) {
            $this->clearBuffer();
            $this->event->del();
            @fclose($args[0]);
            $this->event->exitLoop();
        } else {
            $this->setLastRecBuff($payload);
            $this->appendRecvBuffer($payload);
        }
    }

    public function consumeBuffer(): void
    {
        $args = func_get_args();
        $buffer = $this->getWriteBuffer();
        if (strlen($buffer) == 0) {
            return;
        }
        if (!is_resource($args[0])) {
            $this->clearBuffer();
            $this->event->del();
            $this->event->exitLoop();
        }
        $len = @fwrite($args[0], $buffer);
        if ($len !== false) {
            $this->setWriteBuffer(substr($buffer, $len));
        } else {
            @fclose($args[0]);
            $this->clearBuffer();
            $this->event->del();
            $this->event->exitLoop();
        }
    }

    public function getOne(): string
    {
        return $this->one;
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param string $event
     * @throws Exception
     */
    private function hook(string $event, ...$args)
    {
        $callbacks = $this->client->getCallbacks();
        if (!in_array($event, array_keys($callbacks))) {
            throw new Exception(sprintf('callback %s not found', $event));
        }
        call_user_func($callbacks[$event], ...$args);
    }

    private function clearBuffer()
    {
        $this->setRecvBuffer('');
        $this->setWriteBuffer('');
    }
}