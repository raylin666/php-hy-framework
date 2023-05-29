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
namespace Core\Command;

use Core\BaseCommand;
use Core\Constants\Constant;
use Core\Generator\LogicGenerator;
use Core\GeneratorCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\make;

#[Command]
class LogicCommand extends GeneratorCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':logic';

    protected string $description = '生成业务逻辑层命令';

    protected string $help = '[-M|--module [MODULE]] [--path [PATH]] [--disable-event-dispatcher] [--] <name>';

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
    }

    public function handle()
    {
        $module = $this->input->getOption('module');
        $path = $this->input->getOption('path');

        if (! ($moduleInfo = $this->Module()->get($module))) {
            $this->error(sprintf('业务模块 %s 不存在', $module));
            return;
        }

        $generator = make(LogicGenerator::class);
        $result = $generator->withModuleInfo($moduleInfo)->createLogic($this->getNameInput(), $path);
        $this->info(sprintf("业务模块逻辑层 %s 创建成功\n命名空间: %s\n模块位置: %s", $moduleInfo->getName(), $result['namespace'], $result['path']));
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['module', 'M', InputOption::VALUE_OPTIONAL, '请输入所在模块名称', ''],
            ['path', null, InputOption::VALUE_OPTIONAL, '生成模型文件的路径, 只能是相对路径'],
        ];
    }
}
