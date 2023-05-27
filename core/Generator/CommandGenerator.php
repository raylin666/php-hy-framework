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

use RuntimeException;

class CommandGenerator extends Generator
{
    public function __construct()
    {
        parent::__construct();
        $this->withNamespace('Core\\Command');
        $this->withStub('command.stub');
    }

    /**
     * 创建命令行.
     * @param string $name 类名称
     * @param string $path 文件相对路径
     * @return array
     */
    public function createCommand(string $name, string $path = ''): array
    {
        $name = ucfirst($name);
        if (! empty($path)) {
            $this->withNamespace($this->convertNamespaceByPath($path));
        }

        $namespace = $this->convertNamespaceByClass($name);
        $path = $this->convertPathByNamespace($namespace);
        if (is_file($path)) {
            throw new RuntimeException(sprintf('%s 文件 | %s 类已存在', $name, $namespace));
        }

        $this->makeDirectory($path);

        $content = $this->buildClass($name);

        file_put_contents($path, $content);

        $this->openWithIde($path);

        return [
            'path' => $path,
            'namespace' => $namespace,
            'class' => $name,
            'content' => $content,
        ];
    }
}
