<?php

namespace App\Http\Controllers;

class PoolMasterController extends Controller
{
    public function index()
    {
        return view('masters.pool.index', [
            'apiBearerToken' => (string) config('services.webapp.api_bearer_token'),
        ]);
    }
}
