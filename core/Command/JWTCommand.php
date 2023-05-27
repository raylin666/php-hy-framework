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
use Hyperf\Command\Annotation\Command;
use Hyperf\Stringable\Str;
use Symfony\Component\Console\Input\InputArgument;

#[Command]
class JWTCommand extends BaseCommand
{
    protected ?string $name = BaseCommand::PREFIX_NAME . ':jwt';

    protected string $description = '生成 JWT 系统密钥';

    protected string $help = '[--disable-event-dispatcher] [--] <name>';

    public function configure()
    {
        parent::configure();
        $this->setHelp(Constant::RUN_COMMAND . ' ' . $this->name . ' ' . $this->help);
        $this->setDescription($this->description);
        $this->addUsage('JWT_SECRET [将会在 .env 配置文件自动追加JWT密钥配置]');
    }

    public function handle()
    {
        $name = Str::upper(trim($this->input->getArgument('name')));
        if (! str_starts_with($name, 'JWT_')) {
            $this->warn('生成的密钥名称, 必须以 `JWT_` 开头');
            return;
        }

        if (strlen($name) <= 4) {
            $this->warn('生成的密钥名称必须大于4位');
            return;
        }

        $path = BASE_PATH . '/.env';
        if (! file_exists($path)) {
            $this->error('.env 配置文件不存在');
            return;
        }

        $key = base64_encode(random_bytes(64));

        if (Str::contains(file_get_contents($path), $name) === false) {
            file_put_contents($path, "\n{$name}={$key}\n", FILE_APPEND);
        } else {
            file_put_contents($path, preg_replace(
                "~{$name}\\s*=\\s*[^\n]*~",
                "{$name}=\"{$key}\"",
                file_get_contents($path)
            ));
        }

        $this->info(sprintf("JWT 系统密钥 %s 生成成功\n文件位置: %s\n系统密钥: %s", $name, $path, $key));
    }

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, '请输入要生成的密钥名称, 必须以 `JWT_` 开头'],
        ];
    }
}
