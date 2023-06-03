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

use Hyperf\Validation\Request\FormRequest;

abstract class FormRequestAbstract extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Public rules.
     */
    public function commonRules(): array
    {
        return [];
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $operation = $this->getOperation();
        $method = $operation . 'Rules';
        $rules = ($operation && method_exists($this, $method)) ? $this->{$method}() : [];
        return array_merge($rules, $this->commonRules());
    }

    protected function getOperation(): ?string
    {
        $path = explode('/', $this->path());
        do {
            $operation = array_pop($path);
        } while (is_numeric($operation));

        return $operation;
    }
}
