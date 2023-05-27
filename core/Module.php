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

use Core\Contract\ModuleInfoInterface;
use Hyperf\Support\Filesystem\Filesystem;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

use function Hyperf\Support\make;

class Module
{
    /**
     * 模块路径.
     */
    private string $path = '';

    /**
     * @var ModuleInfoInterface[]
     */
    private array $modules = [];

    /**
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct()
    {
        $this->path = BASE_PATH . '/app';
        $this->scanModule();
    }

    /**
     * @return mixed
     */
    public function getPath(): string
    {
        return $this->path . DIRECTORY_SEPARATOR;
    }

    /**
     * 获取具体模块信息.
     * @param string $name 模块名称
     */
    public function get(string $name): ?ModuleInfoInterface
    {
        return $this->modules[ucfirst($name)] ?? null;
    }

    /**
     * 获取模块列表.
     * @return ModuleInfoInterface[]
     */
    public function list(): array
    {
        return $this->modules;
    }

    /**
     * 遍历获取所有模块信息.
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    protected function scanModule(): void
    {
        $modules = glob(self::getPath() . '*');
        $fs = container()->get(Filesystem::class);
        foreach ($modules as &$mod) {
            if (is_dir($mod)) {
                $modInfo = $mod . '/config.json';
                if (file_exists($modInfo)) {
                    $values = json_decode($fs->sharedGet($modInfo), true);
                    $module = make(ModuleInfoInterface::class);
                    if (! isset($values['name'])) {
                        continue;
                    }

                    $module->withName($values['name']);
                    isset($values['label']) && $module->withLabel($values['label']);
                    isset($values['description']) && $module->withDescription($values['description']);
                    isset($values['installed']) && $module->withInstalled($values['installed']);
                    isset($values['enabled']) && $module->withEnabled($values['enabled']);
                    isset($values['version']) && $module->withVersion($values['version']);
                    isset($values['sort']) && $module->withSort($values['sort']);
                    $this->modules[basename($mod)] = $module;
                }
            }
        }
    }
}
