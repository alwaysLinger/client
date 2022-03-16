<?php

declare(strict_types=1);

class ClientProxy
{
    public function __construct(
        protected string $addr,
        protected string $mode,
        protected array  $context = [],
    )
    {
    }

    /**
     * @return string
     */
    public function getAddr(): string
    {
        return $this->addr;
    }

    /**
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    public function setClientAttributes(...$arguments): void
    {

    }
}