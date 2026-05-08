<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class CurrencyMasterController extends Controller
{
    public function index()
    {
        return view('masters.currency.index');
    }
}
