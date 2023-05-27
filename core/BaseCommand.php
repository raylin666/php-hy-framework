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
namespace Core;

use Hyperf\Command\Command as HyperfCommand;

use function Hyperf\Support\make;

abstract class BaseCommand extends HyperfCommand
{
    /**
     * 命令行前缀名称.
     */
    public const PREFIX_NAME = 'core';

    /**
     * 获取模块.
     */
    private ?Module $module = null;

    /**
     * 获取模块对象
     */
    protected function Module(): Module
    {
        return ($this->module instanceof Module) ? $this->module : make(Module::class);
    }
}
