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

use Core\Helper\ApplicationHelper;
use Core\Module;
use Hyperf\CodeParser\Project;
use Hyperf\Contract\ConfigInterface;
use RuntimeException;

use function Hyperf\Support\make;

abstract class Generator
{
    public const STUB_REPLACE_NAMESPACE = '%NAMESPACE%';

    public const STUB_REPLACE_CLASS = '%CLASS%';

    public const STUB_REPLACE_USES = '%USES%';

    public const STUB_REPLACE_INHERITANCE = '%INHERITANCE%';

    /**
     * 命名空间.
     */
    protected string $namespace = '';

    /**
     * 模版目录.
     */
    protected string $stubDir;

    /**
     * 完整的模版位置.
     */
    protected string $stub = '';

    /**
     * 业务模块.
     */
    protected ?Module $module = null;

    public function __construct()
    {
        $this->stubDir = BASE_PATH . '/core/Generator/Stubs/';
    }

    /**
     * 获取模版目录.
     */
    public function getStubDir(): string
    {
        return $this->stubDir;
    }

    public function getNamespace(): string
    {
        if (empty($this->namespace)) {
            throw new RuntimeException('请先调用 withNamespace 函数设置命名空间');
        }

        return $this->namespace;
    }

    /**
     * 设置命名空间.
     * @return $this
     */
    public function withNamespace(string $namespace): static
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function getStub(): string
    {
        if (empty($this->stub)) {
            throw new RuntimeException('请先调用 withStub 函数设置具体的模版文件');
        }

        return $this->stub;
    }

    /**
     * 设置模版, 一般为文件名称。如有必要, 也可以是相对路径文件名称.
     * @return $this
     */
    public function withStub(string $filename): static
    {
        $this->stub = str_replace('//', '/', $this->getStubDir() . $filename);
        return $this;
    }

    /**
     * 获取模块类.
     */
    public function getModule(): Module
    {
        if (! $this->module instanceof Module) {
            $this->module = make(Module::class);
        }

        return $this->module;
    }

    /**
     * 设置模块类.
     * @param Module $module 模块类
     * @return $this
     */
    public function withModule(Module $module): static
    {
        $this->module = $module;
        return $this;
    }

    /**
     * 根据类名称解析转换为命名空间格式.
     * @param string $class 类名称
     */
    protected function convertNamespaceByClass(string $class): string
    {
        $class = ltrim($class, '\\/');
        $class = str_replace('/', '\\', $class);
        return $this->getNamespace() . '\\' . $class;
    }

    /**
     * 根据文件相对路径解析转换为命名空间格式.
     * @param string $path 文件相对路径, 如需要绝对路径可以先调用 withNamespace 函数修改掉原命名空间在调用该函数
     * @param string $name 类名称
     */
    protected function convertNamespaceByPath(string $path, string $name = ''): string
    {
        $namespace = ltrim($path, '\\/');
        $namespace = str_replace(['///', '//', '/'], '\\', $namespace);
        if (empty($name)) {
            return $this->getNamespace() . '\\' . rtrim($namespace, '\\/');
        }

        $name = ltrim($name, '\\/');
        $name = str_replace('/', '\\', $name);
        return $this->getNamespace() . '\\' . rtrim($namespace, '\\/') . '\\' . $name;
    }

    /**
     * 根据命名空间转换为目标类路径.
     * @param string $namespace 命名空间
     * @param bool $isDir 是否目录
     */
    protected function convertPathByNamespace(string $namespace, bool $isDir = false): string
    {
        $project = new Project();
        return BASE_PATH . '/' . ($isDir ? $project->path($namespace, '') : $project->path($namespace));
    }

    /**
     * 使用给定的类名称生成类文件内容.
     * @param string $class 类名称
     */
    protected function buildClass(string $class): string
    {
        $content = file_get_contents($this->getStub());

        return $this->stubReplaceClass($class, $this->stubReplaceNamespace($content));
    }

    /**
     * 替换给定存根的命名空间.
     * @param string $content 文件内容
     */
    protected function stubReplaceNamespace(string $content): string
    {
        return str_replace(static::STUB_REPLACE_NAMESPACE, $this->getNamespace(), $content);
    }

    /**
     * 替换给定存根的类名.
     * @param string $class 类名称
     * @param string $content 文件内容
     */
    protected function stubReplaceClass(string $class, string $content): string
    {
        return str_replace(static::STUB_REPLACE_CLASS, $class, $content);
    }

    /**
     * 如有必要(目录不存在时候)，为类生成目录.
     * @param string $path 目录地址
     * @param bool $isDir 是否目录, 如果不是目录则认为 $path 是文件, 会通过 dirname 过滤一层路径
     * @param int $permissions 权限
     */
    protected function makeDirectory(string $path, bool $isDir = false, int $permissions = 0755): string
    {
        $dir = $isDir ? $path : dirname($path);
        if (! is_dir($dir)) {
            mkdir($dir, $permissions, true);
        }

        return $path;
    }

    /**
     * 使用配置的IDE打开结果文件路径.
     * @param string $path 文件位置
     */
    protected function openWithIde(string $path): void
    {
        $ide = (string) ApplicationHelper::getConfig()->get('devtool.ide');
        $openEditorUrl = $this->getEditorUrl($ide);

        if (! $openEditorUrl) {
            return;
        }

        $url = sprintf($openEditorUrl, $path);
        switch (PHP_OS_FAMILY) {
            case 'Windows':
                exec('explorer ' . $url);
                break;
            case 'Linux':
                exec('xdg-open ' . $url);
                break;
            case 'Darwin':
                exec('open ' . $url);
                break;
        }
    }

    /**
     * 按名称获取编辑器文件打开器URL.
     * @param string $ide IDE 配置值
     */
    protected function getEditorUrl(string $ide): string
    {
        return match ($ide) {
            'sublime' => 'subl://open?url=file://%s',
            'textmate' => 'txmt://open?url=file://%s',
            'emacs' => 'emacs://open?url=file://%s',
            'macvim' => 'mvim://open/?url=file://%s',
            'phpstorm' => 'phpstorm://open?file=%s',
            'idea' => 'idea://open?file=%s',
            'vscode' => 'vscode://file/%s',
            'vscode-insiders' => 'vscode-insiders://file/%s',
            'vscode-remote' => 'vscode://vscode-remote/%s',
            'vscode-insiders-remote' => 'vscode-insiders://vscode-remote/%s',
            'atom' => 'atom://core/open/file?filename=%s',
            'nova' => 'nova://core/open/file?filename=%s',
            'netbeans' => 'netbeans://open/?f=%s',
            'xdebug' => 'xdebug://%s',
            default => '',
        };
    }
}
