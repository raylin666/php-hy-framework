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
namespace Core\Generator;

use Core\Contract\ModuleInfoInterface;
use RuntimeException;

class ControllerGenerator extends Generator
{
    protected ?ModuleInfoInterface $moduleInfo = null;

    public function __construct()
    {
        parent::__construct();
    }

    public function getModuleInfo(): ?ModuleInfoInterface
    {
        return $this->moduleInfo;
    }

    public function withModuleInfo(ModuleInfoInterface $moduleInfo): static
    {
        $this->moduleInfo = $moduleInfo;
        return $this;
    }

    /**
     * 创建业务模块父级控制器.
     */
    public function createModuleController(): array
    {
        // 未设置 Module 暂不支持创建
        if (empty($this->getModuleInfo())) {
            throw new RuntimeException('请调用 withModuleInfo 函数设置业务模块信息');
        }

        if (empty($this->getModuleInfo()->getName())) {
            throw new RuntimeException('业务模块名称为空');
        }

        $class = 'Controller';
        $this->withStub('parent_controller.stub');
        $this->withNamespace('App\\' . $this->moduleInfo->getName() . '\\Controller');
        $path = $this->convertPathByNamespace($this->getNamespace(), true) . '/' . $class . '.php';
        if (is_file($path)) {
            throw new RuntimeException(sprintf('%s 类已存在', $this->getNamespace()));
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($class);

        file_put_contents($path, $content);

        return [
            'path' => $path,
            'namespace' => $this->getNamespace(),
            'class' => $class,
            'content' => $content,
        ];
    }

    /**
     * 创建业务模块控制器.
     */
    public function createController(string $name, ?string $path = null): array
    {
        if (! $this->getModuleInfo() instanceof ModuleInfoInterface) {
            throw new RuntimeException('请调用 withModuleInfo 函数设置业务模块信息');
        }

        if (empty($this->getModuleInfo()->getName())) {
            throw new RuntimeException('业务模块名称为空');
        }

        $uses = '';
        $class = ucfirst($name);
        $this->withStub('controller.stub');
        $this->withNamespace('App\\' . $this->getModuleInfo()->getName() . '\\Controller');
        if ($path) {
            $uses = $this->getNamespace() . '\\Controller';
            $this->withNamespace($this->convertNamespaceByPath($path));
        }

        $namespace = $this->getNamespace();
        $path = $this->convertPathByNamespace($namespace, true) . '/' . $class . '.php';
        if (file_exists($path)) {
            throw new RuntimeException(sprintf('业务模块控制器 %s 已存在', $namespace));
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($class);
        $content = $this->stubModelReplaceUses($uses, $content);

        file_put_contents($path, $content);

        return [
            'path' => $path,
            'name' => $name,
            'module' => $this->getModuleInfo()->getName(),
            'namespace' => $namespace,
            'class' => $class,
            'content' => $content,
        ];
    }

    /**
     * 替换给定存根的引入类命名空间名称.
     * @param string $uses 引入类命名空间名称
     * @param string $content 文件内容
     */
    protected function stubModelReplaceUses(string $uses, string $content): string
    {
        $uses = $uses ? "use {$uses};" : '';
        return str_replace(static::STUB_REPLACE_USES, $uses, $content);
    }
}
