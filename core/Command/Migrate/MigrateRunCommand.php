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
namespace Core\Command\Migrate;

use Core\BaseCommand;
use Core\Constants\Constant;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\ConfirmableTrait;
use Hyperf\Database\Commands\Migrations\BaseCommand as MigrationsBaseCommand;
use Hyperf\Database\Migrations\Migrator;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class MigrateRunCommand extends MigrationsBaseCommand
{
    use ConfirmableTrait;

    protected ?string $name = BaseCommand::PREFIX_NAME . ':migrate-run';

    protected string $description = '运行数据表迁移';

    protected string $help = '[--database [DATABASE]] [--force] [--path [PATH]] [--realpath] [--pretend] [--seed] [--step] [--disable-event-dispatcher]';

    /**
     * @var Migrator 迁移类
     */
    protected Migrator $migrator;

    public function __construct(Migrator $migrator)
    {
        parent::__construct();
        $this->migrator = $migrator;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
        $this->addUsage('--database default');
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->prepareDatabase();

        // Next, we will check to see if a path option has been defined. If it has
        // we will use the path relative to the root of this installation folder
        // so that migrations may be run for any path within the applications.
        $this->migrator->setOutput($this->output)
            ->run($this->getMigrationPaths(), [
                'pretend' => $this->input->getOption('pretend'),
                'step' => $this->input->getOption('step'),
            ]);

        // Finally, if the "seed" option has been given, we will re-run the database
        // seed task to re-populate the database, which is convenient when adding
        // a migration and a seed at the same time, as it is only this command.
        if ($this->input->getOption('seed') && ! $this->input->getOption('pretend')) {
            $this->call('db:seed', ['--force' => true]);
        }

        foreach ($this->getMigrationPaths() as $index => $migrationPath) {
            $this->info('数据表迁移文件位置 ' . $index . ' : ' . $migrationPath);
        }
    }

    /**
     * 准备迁移数据库以便运行.
     */
    protected function prepareDatabase()
    {
        $this->migrator->setConnection($this->input->getOption('database') ?? 'default');

        if (! $this->migrator->repositoryExists()) {
            $this->call('migrate:install', array_filter([
                '--database' => $this->input->getOption('database'),
            ]));
        }
    }

    /**
     * 获取迁移路径（由 --path 选项或默认位置指定）.
     */
    protected function getMigrationPath(): string
    {
        return BASE_PATH . '/core/Database/Migrations';
    }

    protected function getOptions(): array
    {
        return [
            ['database', null, InputOption::VALUE_OPTIONAL, '使用的数据库连接名称'],
            ['force', null, InputOption::VALUE_NONE, '强制操作在生产时运行'],
            ['path', null, InputOption::VALUE_OPTIONAL, '要执行的迁移文件的路径'],
            ['realpath', null, InputOption::VALUE_NONE, '指示提供的任何迁移文件路径都是预解析的绝对路径'],
            ['pretend', null, InputOption::VALUE_NONE, '转储将要运行的SQL查询'],
            ['seed', null, InputOption::VALUE_NONE, '指示是否应重新运行种子任务'],
            ['step', null, InputOption::VALUE_NONE, '强制运行迁移，以便可以单独回滚'],
        ];
    }
}
