<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class DepartmentManagerMasterController extends Controller
{
    public function index(): View
    {
        return view('masters.department-managers.index');
    }
}
