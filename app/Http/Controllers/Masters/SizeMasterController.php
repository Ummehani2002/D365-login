<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;

class SizeMasterController extends Controller
{
    public function index()
    {
        return view('masters.size.index');
    }
}
