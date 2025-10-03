<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class StationApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Station::query();

        // Filter by location if provided
        if ($request->has('latitude') && $request->has('longitude')) {
            $lat = $request->latitude;
            $lng = $request->longitude;
            $radius = $request->radius ?? 10; // Default 10km radius

            $query->selectRaw("*,
                ( 6371 * acos( cos( radians(?) ) *
                  cos( radians( latitude ) ) *
                  cos( radians( longitude ) - radians(?) ) +
                  sin( radians(?) ) *
                  sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
                ->having('distance', '<', $radius)
                ->orderBy('distance');
        }

        // Filter by availability
        if ($request->has('available')) {
            $query->where('is_available', $request->boolean('available'));
        }

        // Filter by connector type
        if ($request->has('connector_type')) {
            $query->where('connector_type', $request->connector_type);
        }

        $stations = $query->paginate($request->per_page ?? 15);

        return response()->json([
            'success' => true,
            'data' => $stations
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'address' => 'required|string',
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'connector_type' => 'required|string',
            'power_output' => 'required|numeric|min:0',
            'pricing_per_hour' => 'required|numeric|min:0',
            'is_available' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $station = Station::create($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Station created successfully',
            'data' => $station
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        // Update available slots before returning data
        $station->updateAvailableSlots();

        // Refresh the model to get updated available_slots
        $station->refresh();

        return response()->json([
            'success' => true,
            'data' => $station
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'address' => 'sometimes|string',
            'latitude' => 'sometimes|numeric|between:-90,90',
            'longitude' => 'sometimes|numeric|between:-180,180',
            'connector_type' => 'sometimes|string',
            'power_output' => 'sometimes|numeric|min:0',
            'pricing_per_hour' => 'sometimes|numeric|min:0',
            'is_available' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $station->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Station updated successfully',
            'data' => $station
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $station = Station::find($id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        $station->delete();

        return response()->json([
            'success' => true,
            'message' => 'Station deleted successfully'
        ]);
    }

    /**
     * Get nearby stations
     */
    public function nearby(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'latitude' => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
            'radius' => 'sometimes|numeric|min:1|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()
            ], 422);
        }

        $lat = $request->latitude;
        $lng = $request->longitude;
        $radius = $request->radius ?? 10;

        $stations = Station::selectRaw("*,
            ( 6371 * acos( cos( radians(?) ) *
              cos( radians( latitude ) ) *
              cos( radians( longitude ) - radians(?) ) +
              sin( radians(?) ) *
              sin( radians( latitude ) ) ) ) AS distance", [$lat, $lng, $lat])
            ->having('distance', '<', $radius)
            ->orderBy('distance')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $stations
        ]);
    }

    /**
     * Get station availability
     */
    public function availability($id)
    {
        $station = Station::with(['bookings' => function($query) {
            $query->where('status', 'active')
                  ->where('start_time', '<=', now())
                  ->where('end_time', '>=', now());
        }])->find($id);

        if (!$station) {
            return response()->json([
                'success' => false,
                'message' => 'Station not found'
            ], 404);
        }

        $isAvailable = $station->is_available && $station->bookings->isEmpty();

        return response()->json([
            'success' => true,
            'data' => [
                'station_id' => $station->id,
                'name' => $station->name,
                'is_available' => $isAvailable,
                'current_bookings' => $station->bookings->count(),
                'next_available_time' => $station->bookings->min('end_time')
            ]
        ]);
    }
}
