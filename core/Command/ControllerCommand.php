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
use Core\Generator\ControllerGenerator;
use Core\GeneratorCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;

use function Hyperf\Support\make;

#[Command]
class ControllerCommand extends GeneratorCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':controller';

    protected string $description = '生成控制器命令';

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

        $generator = make(ControllerGenerator::class);
        $result = $generator->withModuleInfo($moduleInfo)->createController($this->getNameInput(), $path);
        var_dump($result);
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
