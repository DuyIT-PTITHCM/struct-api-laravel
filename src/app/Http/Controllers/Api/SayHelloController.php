<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;

class SayHelloController extends Controller
{
    public function sayHello()
    {
        return response()->json(['message' => 'Hello', 'status' => 'OK']);
    }
}
