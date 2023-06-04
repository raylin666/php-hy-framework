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
namespace Core\Helper;

use Hyperf\Context\ApplicationContext;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Logger\LoggerFactory;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

class ApplicationHelper
{
    /**
     * 获取容器实例.
     * @return ContainerInterface
     */
    public static function getContainer(): ContainerInterface
    {
        return ApplicationContext::getContainer();
    }

    /**
     * 获取配置实例.
     */
    public static function getConfig(): ConfigInterface
    {
        return self::getContainer()->get(ConfigInterface::class);
    }

    /**
     * 获取日志实例.
     */
    public static function getLogger(): LoggerInterface
    {
        return self::getContainer()->get(LoggerFactory::class);
    }
}
