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
namespace Core\Command\Seeder;

use Core\BaseCommand;
use Core\Constants\Constant;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\ConfirmableTrait;
use Hyperf\Database\Commands\Seeders\BaseCommand as SeedersBaseCommand;
use Hyperf\Database\Seeders\Seed;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class SeederRunCommand extends SeedersBaseCommand
{
    use ConfirmableTrait;

    protected ?string $name = BaseCommand::PREFIX_NAME . ':seeder-run';

    protected string $description = '运行数据表迁移种子数据';

    protected string $help = '[--path [PATH]] [--realpath] [--database [DATABASE]] [--force] [--disable-event-dispatcher]';

    /**
     * @var Seed
     */
    protected Seed $seed;

    /**
     * @param Seed $seed
     */
    public function __construct(Seed $seed)
    {
        parent::__construct();
        $this->seed = $seed;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
    }

    public function handle()
    {
        if (! $this->confirmToProceed()) {
            return;
        }

        $this->seed->setOutput($this->output);

        if ($this->input->hasOption('database') && $this->input->getOption('database')) {
            $this->seed->setConnection($this->input->getOption('database'));
        }

        $this->seed->run([$this->getSeederPath()]);
    }

    protected function getSeederPath(): string
    {
        if (! is_null($targetPath = $this->input->getOption('path'))) {
            return ! $this->usingRealPath()
                ? BASE_PATH . '/' . $targetPath
                : $targetPath;
        }

        return BASE_PATH . '/core/Database/Seeders';
    }

    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, '创建数据表迁移文件目录位置'],
            ['realpath', null, InputOption::VALUE_NONE, '指示提供的任何迁移文件路径都是预解析的绝对路径'],
            ['database', null, InputOption::VALUE_OPTIONAL, '使用的数据库连接名称'],
            ['force', null, InputOption::VALUE_NONE, '强制操作在生产时运行'],
        ];
    }
}
