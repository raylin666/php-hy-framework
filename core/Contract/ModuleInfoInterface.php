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
namespace Core\Contract;

interface ModuleInfoInterface
{
    public function withName(string $value): ModuleInfoInterface;

    public function getName(): string;

    public function withLabel(string $value): ModuleInfoInterface;

    public function getLabel(): string;

    public function withDescription(string $value): ModuleInfoInterface;

    public function getDescription(): string;

    public function withInstalled(bool $value): ModuleInfoInterface;

    public function getInstalled(): bool;

    public function withEnabled(bool $value): ModuleInfoInterface;

    public function getEnabled(): bool;

    public function withVersion(string $value): ModuleInfoInterface;

    public function getVersion(): string;

    public function toArray(): array;
}
