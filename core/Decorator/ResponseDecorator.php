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
namespace Core\Decorator;

use Core\Constants\HttpErrorCode;

class ResponseDecorator
{
    public const OK_CODE = 200;

    public bool $ok = true;

    public int $code = self::OK_CODE;

    public string $message = 'OK';

    public $data;

    public function isOK(): bool
    {
        return $this->ok;
    }

    public function withCode(int $code): self
    {
        $this->code = $code;
        $this->message = HttpErrorCode::getMessage($code);
        ($code !== self::OK_CODE) && $this->ok = false;
        return $this;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function withMessage(string $message): self
    {
        $this->message = $message;
        return $this;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function withData($data): self
    {
        $this->data = $data;
        return $this;
    }

    public function getData()
    {
        return $this->data;
    }

    public function toArray(): array
    {
        return [
            'ok' => $this->isOK(),
            'code' => $this->getCode(),
            'data' => $this->getData(),
            'message' => $this->getMessage(),
        ];
    }
}
