<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class PoolMasterController extends Controller
{
    public function index()
    {
        return view('masters.pool.index');
    }
}
