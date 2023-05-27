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
use Core\Generator\CommandGenerator;
use Core\GeneratorCommand;
use Hyperf\Command\Annotation\Command;
use Symfony\Component\Console\Input\InputOption;
use function Hyperf\Support\make;

#[Command]
class CommandCommand extends GeneratorCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':command';

    protected string $description = '创建命令文件';

    protected string $help = '[--path [PATH]] [--disable-event-dispatcher] [--] <name>';

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
        $this->addUsage('JWTCommand');
        $this->addUsage('--path Migrate MigrateCommand');
    }

    public function handle()
    {
        $path = $this->input->getOption('path');
        /** @var CommandGenerator $generator */
        $generator = make(CommandGenerator::class);
        $result = $generator->createCommand($this->getNameInput(), $path);
        $this->info(sprintf("类文件 %s 创建成功\n命名空间: %s\n文件位置: %s", $result['class'], $result['namespace'], $result['path']));
    }

    public function getOptions(): array
    {
        return [
            ['path', null, InputOption::VALUE_OPTIONAL, '文件相对路径', ''],
        ];
    }
}
