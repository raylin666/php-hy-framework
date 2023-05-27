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
namespace Core\Model;

use Hyperf\DbConnection\Model\Model as BaseModel;
use Hyperf\ModelCache\Cacheable;
use Hyperf\ModelCache\CacheableInterface;

abstract class Model extends BaseModel implements CacheableInterface
{
    use Cacheable;

    /**
     * 隐藏的字段列表.
     * @var string[]
     */
    protected array $hidden = ['deleted_at'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
    }

    /**
     * 设置主键的值
     */
    public function setPrimaryKeyValue(int|string $value): void
    {
        $this->{$this->primaryKey} = $value;
    }

    public function getPrimaryKeyType(): string
    {
        return $this->keyType;
    }

    public function save(array $options = []): bool
    {
        return parent::save($options);
    }

    public function update(array $attributes = [], array $options = []): bool
    {
        return parent::update($attributes, $options);
    }
}
