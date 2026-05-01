<?php

namespace App\Http\Controllers;

class CurrencyMasterController extends Controller
{
    public function index()
    {
        return view('masters.currency.index');
    }
}
