<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class SenangPayController extends Controller
{
    public function handleReturn(Request $request)
    {
        Log::info('handleReturn: ' . json_encode($request->all()));
        return 'Payment successful';
    }

    public function handleCallback(Request $request)
    {
        Log::info('handleCallback: ' . json_encode($request->all()));

        return response('OK', 200);
    }
}

