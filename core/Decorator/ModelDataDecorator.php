<?php

declare(strict_types=1);

namespace Core\Decorator;

class ModelDataDecorator
{
    public function __construct(protected string $class, protected array $columns)
    {
    }

    public function getColumns(): array
    {
        return $this->columns;
    }

    public function setColumns(array $columns): static
    {
        $this->columns = $columns;
        return $this;
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): static
    {
        $this->class = $class;
        return $this;
    }
}
