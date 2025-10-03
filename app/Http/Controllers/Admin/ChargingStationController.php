<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Station;

class ChargingStationController extends Controller
{
    public function index()
    {
        return response()->json(Station::all());
    }

    public function store(Request $request)
    {
        $station = Station::create($request->all());
        return response()->json($station, 201);
    }

    public function show($id)
    {
        return response()->json(Station::findOrFail($id));
    }

    public function update(Request $request, $id)
    {
        $station = Station::findOrFail($id);
        $station->update($request->all());
        return response()->json($station);
    }

    public function destroy($id)
    {
        Station::destroy($id);
        return response()->json(['message' => 'Deleted successfully']);
    }
}
