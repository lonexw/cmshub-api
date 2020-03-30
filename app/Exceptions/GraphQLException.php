<?php

namespace App\Exceptions;

use Exception;
use Nuwave\Lighthouse\Exceptions\RendersErrorsExtensions;

class GraphQLException extends Exception implements RendersErrorsExtensions
{
    private $data;
    private $category;

    public function __construct(string $message = "", $data = [], int $error_code = -1, $category = "")
    {
        parent::__construct($message, $error_code);
        $this->data     = $data;
        $this->category = $category;
    }

    public function extensionsContent(): array
    {
        return [
            'err_code'    => $this->code,
            'err_message' => $this->message,
            'data'        => $this->data,
        ];
    }

    public function isClientSafe(): bool
    {
        return true;
    }

    public function getCategory(): string
    {
        return $this->category;
    }

}
