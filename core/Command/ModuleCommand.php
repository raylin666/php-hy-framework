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
use Core\Decorator\ModuleInfoDecorator;
use Core\Generator\ModuleGenerator;
use Core\GeneratorCommand;
use Core\Helper\ConsoleTableHelper;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;
use function Hyperf\Support\make;

#[Command]
class ModuleCommand extends GeneratorCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':module';

    protected string $description = '生成业务模块/模块配置更新';

    protected string $help = '[-L|--label [LABEL]] [-D|--description [DESCRIPTION]] [--disabled] [-C|--is-update-config] [--disable-event-dispatcher] [--] <name>';

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
        $this->addUsage('LIST 获取业务模块列表(必须大写)');
        $this->addUsage('xxx 模块名称不能为`LIST`, 否则为获取业务模块列表');
    }

    public function handle()
    {
        // 获取业务模块列表
        if ($this->getNameInput() == 'LIST') {
            $modules = $this->Module()->list();
            $table = new ConsoleTableHelper();
            $table->setHeader(['Name', 'Label', 'Description', 'Version', 'Install', 'Enable']);
            foreach ($modules as $mod) {
                $table->addRow([
                    $mod->getName() ?? 'NULL',
                    $mod->getLabel() ?? 'NULL',
                    $mod->getDescription() ?? 'NULL',
                    $mod->getVersion() ?? 'NULL',
                    $mod->getInstalled() === true ? 'YES' : 'NO',
                    $mod->getEnabled() === true ? 'YES' : 'NO',
                ]);
            }

            echo $table->render();
            return;
        }

        $label = $this->input->getOption('label');
        $description = $this->input->getOption('description');
        $disabled = $this->input->getOption('disabled');
        $isUpdateConfig = $this->input->getOption('is-update-config');

        $generator = make(ModuleGenerator::class);
        $moduleInfo = make(ModuleInfoDecorator::class);
        $moduleInfo->withName($this->getNameInput());
        if ($label && is_string($label)) {
            $moduleInfo->withLabel($label);
        }
        if ($description && is_string($description)) {
            $moduleInfo->withDescription($description);
        }
        if ($disabled) {
            $moduleInfo->withEnabled(false);
        }

        // 业务模块配置更新
        if (is_bool($isUpdateConfig) && $isUpdateConfig) {
            $result = $generator->updateConfigJson($moduleInfo);
            $this->info(sprintf("业务模块 %s 配置更新成功\n模块文件位置: %s\n文件更新内容: \n%s", $moduleInfo->getName(), $result['path'], $result['content']));
            return;
        }

        // 业务模块创建
        $result = $generator->withModule($this->Module())->createModule($moduleInfo);
        $this->info(sprintf("业务模块 %s 创建成功\n模块位置: %s", $moduleInfo->getName(), $result['path']));
    }

    /**
     * @return array[]
     */
    protected function getOptions(): array
    {
        return [
            ['label', 'L', InputOption::VALUE_OPTIONAL, '请输入生成的模块标签'],
            ['description', 'D', InputOption::VALUE_OPTIONAL, '请输入生成的模块描述'],
            ['disabled', null, InputOption::VALUE_NONE, '禁用该模块, 需后续开启'],
            ['is-update-config', 'C', InputOption::VALUE_NONE, '是否需要更新模块配置文件'],
        ];
    }
}
