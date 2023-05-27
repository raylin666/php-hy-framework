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
use Exception;
use Hyperf\Command\Annotation\Command;
use Hyperf\Database\Commands\Migrations\TableGuesser;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeedersBaseCommand;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class MigrateCommand extends SeedersBaseCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':migrate-gen';

    protected string $description = '生成数据表迁移文件';

    protected string $help = '[--create [CREATE]] [--table [TABLE]] [--path [PATH]] [--realpath] [--disable-event-dispatcher] [--] <name>';

    /**
     * @var MigrationCreator 迁移创建类
     */
    protected MigrationCreator $creator;

    public function __construct(MigrationCreator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
        $this->addUsage('user');
        $this->addUsage('--table api_user user');
    }

    public function handle()
    {
        // It's possible for the developer to specify the tables to modify in this
        // schema operation. The developer may also specify if this table needs
        // to be freshly created, so we can create the appropriate migrations.
        $name = 'create_' . Str::snake(trim($this->input->getArgument('name'))) . '_table';
        $table = $this->input->getOption('table');
        $create = $this->input->getOption('create') ?: false;

        // If no table was given as an option but a create option is given then we
        // will use the "create" option as the table name. This allows the devs
        // to pass a table name into this option as a short-cut for creating.
        if (! $table && is_string($create)) {
            $table = $create;
            $create = true;
        }

        // Next, we will attempt to guess the table name if this the migration has
        // "create" in the name. This will allow us to provide a convenient way
        // of creating migrations that create new tables for the application.
        if (! $table) {
            [$table, $create] = TableGuesser::guess($name);
        }

        // Now we are ready to write the migration out to disk. Once we've written
        // the migration out, we will dump-autoload for the entire framework to
        // make sure that the migrations are registered by the class loaders.
        $this->writeMigration($name, $table, $create);
    }

    /**
     * 将迁移文件写入磁盘.
     * @param string $name 数据表迁移名称
     * @param null|string $table 数据表名称
     * @param bool $create 是否创建
     */
    protected function writeMigration(string $name, ?string $table, bool $create): void
    {
        try {
            $path = $this->creator->create(
                $name,
                $this->getMigrationPath(),
                $table,
                $create
            );

            $file = pathinfo($path, PATHINFO_FILENAME);
            $this->info("创建迁移文件 {$file} 成功\n文件位置: {$path}");
        } catch (Exception $e) {
            $this->error("创建迁移文件失败 {$e->getMessage()}");
        }
    }

    /**
     * 获取迁移路径（由 --path 选项或默认位置指定）.
     */
    protected function getMigrationPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . '/core/Database/Migrations';
    }

    /**
     * 确定给定路径是否为预先解析的 "真实" 绝对路径.
     */
    protected function usingRealPath(): bool
    {
        return $this->input->hasOption('realpath') && $this->input->getOption('realpath');
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, '数据表迁移名称'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['create', null, InputOption::VALUE_OPTIONAL, '用来指定数据表的名称, 但跟 --table 的差异在于该选项是生成创建表的迁移文件，而 --table 是用于修改表的迁移文件'],
            ['table', null, InputOption::VALUE_OPTIONAL, '用来指定数据表的名称, 指定的表名将会默认生成在迁移文件中'],
            ['path', null, InputOption::VALUE_OPTIONAL, '创建数据表迁移文件目录位置'],
            ['realpath', null, InputOption::VALUE_NONE, '指示提供的任何迁移文件路径都是预解析的绝对路径'],
        ];
    }
}
