<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\ContactRequest;
use App\Models\Contact;
use App\Services\AppResponse;
use App\Mail\ContactMessage;

class ContactController extends Controller
{
    public function submit(ContactRequest $request)
    {

        $validatedData = $request->validated();

        return AppResponse::success('Thank you for messaging!', 200);
    }
}