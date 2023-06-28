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
namespace Core\Abstract;

use Core\Contract\BusinessAttributesInterface;
use Core\Contract\ServiceInterface;
use Hyperf\Context\Context;

abstract class ServiceAbstract implements ServiceInterface
{
    /**
     * 魔术方法，从类属性里获取数据.
     * @return mixed|string
     */
    public function __get(string $name)
    {
        // TODO: Implement __get() method.

        return $this->getAttributes()[$name] ?? '';
    }

    /**
     * 把数据设置为类属性.
     */
    public function setAttributes(array $data): void
    {
        // TODO: Implement setAttributes() method.

        Context::set(BusinessAttributesInterface::class, $data);
    }

    /**
     * 获取数据.
     */
    public function getAttributes(): array
    {
        // TODO: Implement getAttributes() method.

        return Context::get(BusinessAttributesInterface::class, []);
    }
}
