<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function endUser()
    {
        return view('dashboards.end_user');
    }

    public function sfqUser()
    {
        return view('dashboards.sfq_user');
    }
}
