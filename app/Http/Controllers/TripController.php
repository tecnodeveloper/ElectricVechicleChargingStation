<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TripController extends Controller
{
    public function summary()
    {
        return response()->json(['summary' => 'Trip summary data']);
    }

    public function routePlan()
    {
        return response()->json(['route' => 'Route plan data']);
    }
}
