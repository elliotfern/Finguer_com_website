<?php

namespace App\Application\Shared\Schema;

use Exception;

class SchemaValidationException extends Exception
{
    private array $errors;

    public function __construct(array $errors)
    {
        parent::__construct('Validation error');
        $this->errors = $errors;
    }

    /**
     * Devuelve errores listos para API response
     */
    public function toApiArray(): array
    {
        return $this->errors;
    }

    /**
     * Compatibilidad (opcional)
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
