<?php

declare(strict_types=1);

namespace Al\Client\Attributes;

use Attribute;

#[Attribute]
class Meta
{
    public function __construct(
        // 当event触发的时候 调用fgets然后 通过定制的流封装协议进行读取保存到wrapper中 每次触发读事件都通过协议进行判断 然后决定是否进行客户端的回调
        public string $event,
        public string $wrapper,
        // wrapper中使用protocol
        public string $protocol,
    )
    {
    }
}