<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Contracts\Validation\Validator;

class TrenchDevsWebApiException extends Exception
{

    /** @var Validator */
    protected $validator;

    public function __construct(Validator $validator)
    {
        $this->validator = $validator;
        parent::__construct("There were validation exceptions", 0, null);
    }

    public function getUniqueErrors(): array
    {
        if (empty($this->validator->errors())) {
            return [];
        }

        return $this->validator->errors()->unique();
    }
}
