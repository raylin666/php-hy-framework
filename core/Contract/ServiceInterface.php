<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */
namespace Core\Contract;

interface ServiceInterface
{
    /**
     * 魔术方法，从类属性里获取数据.
     * @param string $name
     * @return mixed
     */
    public function __get(string $name);

    /**
     * 把数据设置为类属性.
     */
    public function setAttributes(array $data);

    /**
     * 获取数据.
     */
    public function getAttributes(): array;
}
