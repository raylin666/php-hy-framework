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

use Symfony\Component\Console\Input\InputArgument;

abstract class GeneratorCommand extends BaseCommand
{
    protected function getNameInput(): string
    {
        return trim($this->input->getArgument('name'));
    }

    /**
     * 获取控制台命令参数.
     * @return array[]
     */
    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, '给定的名称(类/文件名称/其他, 根据实际命令设定)'],
        ];
    }
}
