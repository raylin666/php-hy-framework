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
use Hyperf\Context\ApplicationContext;
use Psr\Container\ContainerInterface;

if (! function_exists('container')) {
    /**
     * 获取容器实例
     * @return ContainerInterface
     */
    function container(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }
}
