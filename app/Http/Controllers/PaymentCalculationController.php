<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PaymentCalculationController extends Controller
{
    public function index()
    {
        // nanti bisa ambil data dari model jika perlu
        return view('payment_calculation');
    }
}
