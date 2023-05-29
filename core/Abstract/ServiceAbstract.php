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
namespace Core\Abstract;

use Core\Contract\LogicInterface;

abstract class ServiceAbstract
{
    protected LogicInterface $logic;

    public function __construct()
    {
        $this->logic = $this->initializeLogic();
    }

    public function getLogic(): ?LogicInterface
    {
        return $this->logic;
    }

    abstract protected function initializeLogic(): ?LogicInterface;
}
