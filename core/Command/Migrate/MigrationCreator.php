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

use Hyperf\Database\Migrations\MigrationCreator as HyMigrationCreator;

class MigrationCreator extends HyMigrationCreator
{
    public function stubPath(): string
    {
        return BASE_PATH . '/core/Command/Migrate/Stubs';
    }
}
