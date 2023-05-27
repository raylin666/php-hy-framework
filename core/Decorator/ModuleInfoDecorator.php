<?php

declare(strict_types=1);

namespace Core\Decorator;

use Core\Contract\ModuleInfoInterface;

class ModuleInfoDecorator implements ModuleInfoInterface
{
    protected string $name = '';

    protected string $label = '';

    protected string $description = '';

    protected bool $installed = true;

    protected bool $enabled = true;

    protected string $version = '1.0.0';

    public function withName(string $value): ModuleInfoInterface
    {
        // TODO: Implement withName() method.

        $this->name = ucfirst($value);
        return $this;
    }

    public function getName(): string
    {
        // TODO: Implement getName() method.

        return $this->name;
    }

    public function withLabel(string $value): ModuleInfoInterface
    {
        // TODO: Implement withLabel() method.

        $this->label = $value;
        return $this;
    }

    public function getLabel(): string
    {
        // TODO: Implement getLabel() method.

        return $this->label;
    }

    public function withDescription(string $value): ModuleInfoInterface
    {
        // TODO: Implement withDescription() method.

        $this->description = $value;
        return $this;
    }

    public function getDescription(): string
    {
        // TODO: Implement getDescription() method.

        return $this->description;
    }

    public function withInstalled(bool $value): ModuleInfoInterface
    {
        // TODO: Implement withInstalled() method.

        $this->installed = $value;
        return $this;
    }

    public function getInstalled(): bool
    {
        // TODO: Implement getInstalled() method.

        return $this->installed;
    }

    public function withEnabled(bool $value): ModuleInfoInterface
    {
        // TODO: Implement withEnabled() method.

        $this->enabled = $value;
        return $this;
    }

    public function getEnabled(): bool
    {
        // TODO: Implement getEnabled() method.

        return $this->enabled;
    }

    public function withVersion(string $value): ModuleInfoInterface
    {
        // TODO: Implement withVersion() method.

        $this->version = $value;
        return $this;
    }

    public function getVersion(): string
    {
        // TODO: Implement getVersion() method.

        return $this->version;
    }

    public function toArray(): array
    {
        // TODO: Implement toArray() method.

        return [
            'name'          =>  $this->getName(),
            'label'         =>  $this->getLabel(),
            'description'   =>  $this->getDescription(),
            'installed'     =>  $this->getInstalled(),
            'enabled'       =>  $this->getEnabled(),
            'version'       =>  $this->getVersion(),
        ];
    }
}
