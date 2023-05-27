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
use Hyperf\Database\Commands\Seeders\BaseCommand as SeedersBaseCommand;
use Hyperf\Database\Seeders\SeederCreator;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

#[Command]
class SeederCommand extends SeedersBaseCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':seeder-gen';

    protected string $description = '生成数据表迁移种子数据文件';

    protected string $help = '[--path [PATH]] [--realpath] [--disable-event-dispatcher] [--] <name>';

    protected SeederCreator $creator;

    public function __construct(SeederCreator $creator)
    {
        parent::__construct();
        $this->creator = $creator;
    }

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
    }

    public function handle()
    {
        $name = Str::snake(trim($this->input->getArgument('name')));

        $this->writeMigration($name);
    }

    /**
     * 将迁移文件写入磁盘.
     */
    protected function writeMigration(string $name)
    {
        $path = $this->ensureSeederDirectoryAlreadyExist(
            $this->getSeederPath()
        );

        $file = pathinfo($this->creator->create($name, $path), PATHINFO_FILENAME);

        $this->info("创建种子数据文件成功 {$file}");
    }

    protected function ensureSeederDirectoryAlreadyExist(string $path): string
    {
        if (! file_exists($path)) {
            mkdir($path, 0755, true);
        }

        return $path;
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

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, '迁移种子数据名称'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, '创建数据表迁移文件目录位置'],
            ['realpath', null, InputOption::VALUE_NONE, '指示提供的任何迁移文件路径都是预解析的绝对路径'],
        ];
    }
}
