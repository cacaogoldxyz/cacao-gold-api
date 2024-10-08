<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InvalidQueryException extends Exception
{
    protected $message = 'No search query provided.'; 

    public function __construct($message = null)
    {
        if ($message) {
            $this->message = $message;
        }
        parent::__construct($this->message);
    }

    public function render($request)
    {
        return response()->json([
            'error' => 'Invalid query',
            'message' => $this->getMessage()
        ], 400);
    }
}
