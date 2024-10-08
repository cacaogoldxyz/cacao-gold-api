<?php

namespace App\Exceptions;

use Exception;

class InvalidCredentialsException extends Exception
{
    protected $message = 'The provided credentials are incorrect.';
    
    public function __construct()
    {
        parent::__construct($this->message);
    }

    public function render($request)
    {
        return response()->json([
            'message' => $this->message
        ], 401);
    }
}
