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
use Core\Decorator\ModelOptionDecorator;
use Core\Generator\ModelGenerator;
use Core\Helper\ConsoleTableHelper;
use Hyperf\Command\Annotation\Command;
use Hyperf\Contract\ConfigInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

use function Hyperf\Support\make;

#[Command]
class ModelCommand extends BaseCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':model';

    protected string $description = '生成数据表模型';

    protected string $help = '[--path [PATH]] [--module [MODULE]] [-p|--pool [POOL]] [-F|--force-casts] [-P|--prefix [PREFIX]] [-i|--inheritance [INHERITANCE]] [-U|--uses [USES]] [-R|--refresh-fillable] [-M|--table-mapping [TABLE-MAPPING]] [--ignore-tables [IGNORE-TABLES]] [--with-comments] [--with-ide] [--visitors [VISITORS]] [--property-case [PROPERTY-CASE]] [--disable-event-dispatcher] [--] [<table>]';

    /**
     * @var ConfigInterface 配置类
     */
    protected ConfigInterface $config;

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);

        $this->addArgument('table', InputArgument::OPTIONAL, '请输入模块要关联的表名称以生成表模型', '');

        // 生成的目录只能基于现结构化下, 避免对结构化的破坏
        $this->addOption('path', null, InputOption::VALUE_OPTIONAL, '生成模型文件的路径, 只能是相对路径');
        $this->addOption('module', null, InputOption::VALUE_OPTIONAL, '请输入所在模块名称, 默认为公共 Core');
        $this->addOption('pool', 'p', InputOption::VALUE_OPTIONAL, '模型使用的连接池名称, 默认 default', 'default');
        $this->addOption('force-casts', 'F', InputOption::VALUE_NONE, '是否强制生成模型的强制转换');
        $this->addOption('prefix', 'P', InputOption::VALUE_OPTIONAL, '设置模型集的前缀');
        $this->addOption('inheritance', 'i', InputOption::VALUE_OPTIONAL, '设置模型扩展的继承');
        $this->addOption('uses', 'U', InputOption::VALUE_OPTIONAL, '模型的默认类使用');
        $this->addOption('refresh-fillable', 'R', InputOption::VALUE_NONE, '是否为模型生成可填充参数');
        $this->addOption('table-mapping', 'M', InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '模型的表映射');
        $this->addOption('ignore-tables', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '忽略用于创建模型的表');
        $this->addOption('with-comments', null, InputOption::VALUE_NONE, '是否生成模型的属性注释');
        $this->addOption('with-ide', null, InputOption::VALUE_NONE, '是否生成模型的 IDE 文件');
        $this->addOption('visitors', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, '为 Ast Traverser 定制访问者');
        $this->addOption('property-case', null, InputOption::VALUE_OPTIONAL, '选择要使用哪个属性大小写，0:蛇大小写，1:骆驼大小写');
    }

    public function run(InputInterface $input, OutputInterface $output): int
    {
        $this->config = container()->get(ConfigInterface::class);
        return parent::run($input, $output);
    }

    public function handle()
    {
        $table = trim($this->input->getArgument('table'));
        $pool = $this->input->getOption('pool');
        $path = $this->getOption('path', 'commands.gen:model.path', $pool);
        $module = $this->getOption('module', '', $pool, '');
        $prefix = $this->getOption('prefix', 'prefix', $pool, '');
        $uses = $this->getOption('uses', 'commands.gen:model.uses', $pool, '');
        $inheritance = $this->getOption('inheritance', 'commands.gen:model.inheritance', $pool, 'Model');
        $forceCasts = $this->getOption('force-casts', 'commands.gen:model.force_casts', $pool, false);
        $refreshFillable = $this->getOption('refresh-fillable', 'commands.gen:model.refresh_fillable', $pool, true);
        $tableMapping = $this->getOption('table-mapping', 'commands.gen:model.table_mapping', $pool, []);
        $ignoreTables = $this->getOption('ignore-tables', 'commands.gen:model.ignore_tables', $pool, []);
        $withComments = $this->getOption('with-comments', 'commands.gen:model.with_comments', $pool, true);
        $withIde = $this->getOption('with-ide', 'commands.gen:model.with_ide', $pool, false);
        $visitors = $this->getOption('visitors', 'commands.gen:model.visitors', $pool, []);
        $propertyCase = $this->getOption('property-case', 'commands.gen:model.property_case', $pool);

        $modelOption = make(ModelOptionDecorator::class);
        $modelOption
            ->setPool($pool)
            ->setPath($path)
            ->setModule($module)
            ->setPrefix($prefix)
            ->setUses($uses)
            ->setInheritance($inheritance)
            ->setForceCasts($forceCasts)
            ->setIgnoreTables($ignoreTables)
            ->setTableMapping($tableMapping)
            ->setRefreshFillable($refreshFillable)
            ->setWithComments($withComments)
            ->setWithIde($withIde)
            ->setVisitors($visitors)
            ->setPropertyCase($propertyCase);

        $generator = make(ModelGenerator::class);
        if (! empty($module)) {
            if ($moduleInfo = $this->Module()->get($module)) {
                $generator->withModuleInfo($moduleInfo);
            }
        }

        $consoleTable = new ConsoleTableHelper();
        $consoleTable->setHeader(['Table', 'Model', 'Module', 'Pool', 'Success', 'Msg']);

        $result = [];
        if (empty($table)) {
            $result = $generator->createModels($modelOption);
        } else {
            $result[] = $generator->createModel($table, $modelOption);
        }

        if ($result) {
            foreach ($result as $item) {
                $consoleTable->addRow([
                    $item['table'] ?? 'NULL',
                    $item['namespace'] ?? 'NULL',
                    $item['module'] ?: 'NULL',
                    $modelOption->getPool(),
                    $item['message'] ? 'NO' : 'YES',
                    $item['message'] ?: 'NULL',
                ]);
            }
        }

        echo $consoleTable->render();
    }

    protected function getOption(string $name, string $key, string $pool = 'default', $default = null)
    {
        $result = $nonInput = null;
        if ($this->input->hasOption($name)) {
            $result = $this->input->getOption($name);
        }

        if (in_array($name, ['force-casts', 'refresh-fillable', 'with-comments', 'with-ide'])) {
            $nonInput = false;
        }
        if (in_array($name, ['table-mapping', 'ignore-tables', 'visitors'])) {
            $nonInput = [];
        }

        if ($result === $nonInput) {
            $result = empty($key) ? $default : $this->config->get("databases.{$pool}.{$key}", $default);
        }

        return $result;
    }
}
