<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentSimulationController extends Controller
{
    public function show(Transaction $transaction)
    {
        // Load the transaction with its relationships
        $transaction->load('booking');

        return Inertia::render('Payments/PaymentSimulation', [
            'transaction' => $transaction
        ]);
    }
} 