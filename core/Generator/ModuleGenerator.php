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
use Core\Module;
use RuntimeException;

use function Hyperf\Support\make;

class ModuleGenerator extends Generator
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 创建模块.
     * @param ModuleInfoInterface $moduleInfo 新模块信息
     */
    public function createModule(ModuleInfoInterface $moduleInfo): array
    {
        if (! empty($this->getModule()->get($moduleInfo->getName()))) {
            throw new RuntimeException(sprintf('业务模块 %s 已存在', $moduleInfo->getName()));
        }

        $this->withNamespace(sprintf('App\\%s', $moduleInfo->getName()));
        $path = $this->convertPathByNamespace($this->getNamespace(), true);
        $this->makeDirectory($path, true);

        foreach ($this->getGeneratorDirs() as $dir) {
            $this->makeDirectory($path . '/' . $dir, true);
            // 创建基础文件
            switch ($dir) {
                case 'Model':
                    $modelGen = make(ModelGenerator::class);
                    $modelGen->withModuleInfo($moduleInfo)->createModuleModel();
                    break;
                default:
            }
        }

        $this->withStub('config.stub');
        $json = file_get_contents($this->getStub());
        $json = $this->replaceConfigJson($json, $moduleInfo);
        file_put_contents($path . '/config.json', $json);

        return [
            'path' => $path,
            'module' => $moduleInfo,
            'content' => $json,
        ];
    }

    /**
     * 更新模块 JSON 文件.
     * @param ModuleInfoInterface $moduleInfo 新模块信息
     * @return array
     */
    public function updateConfigJson(ModuleInfoInterface $moduleInfo): array
    {
        if (empty($this->getModule()->get($moduleInfo->getName()))) {
            throw new RuntimeException(sprintf('业务模块 %s 不存在', $moduleInfo->getName()));
        }

        $this->withStub('config.stub');
        $json = file_get_contents($this->getStub());
        $this->withNamespace(sprintf('App\\%s', $moduleInfo->getName()));
        $path = $this->convertPathByNamespace($this->getNamespace(), true) . '/config.json';
        $json = $this->replaceConfigJson($json, $moduleInfo);
        file_put_contents($path, $json);

        return [
            'path' => $path,
            'module' => $moduleInfo,
            'content' => $json,
        ];
    }

    /**
     * JSON 配置内容替换.
     * @param string $json 配置文件内容
     * @param ModuleInfoInterface $moduleInfo 模块信息
     */
    protected function replaceConfigJson(string $json, ModuleInfoInterface $moduleInfo): array|string
    {
        $content = str_replace(
            ['{NAME}', '{LABEL}', '{DESCRIPTION}', '{INSTALLED}', '{ENABLED}', '{VERSION}'],
            [
                $moduleInfo->getName(),
                $moduleInfo->getLabel(),
                $moduleInfo->getDescription(),
                $moduleInfo->getInstalled(),
                $moduleInfo->getEnabled(),
                $moduleInfo->getVersion(),
            ],
            $json
        );

        $content = str_replace(['"installed":"1"', '"installed": "1"'], '"installed": true', $content);
        $content = str_replace(['"installed":""', '"installed": ""', '"installed": "0"', '"installed": "0"'], '"installed": false', $content);
        $content = str_replace(['"enabled":"1"', '"enabled": "1"'], '"enabled": true', $content);
        return str_replace(['"enabled":""', '"enabled": ""', '"enabled": "0"', '"enabled": "0"'], '"enabled": false', $content);
    }

    /**
     * 生成的目录列表.
     */
    protected function getGeneratorDirs(): array
    {
        return [
            'Controller',
            'Model',
            'Listener',
            'Request',
            'Service',
            'Mapper',
            'Middleware',
        ];
    }
}
