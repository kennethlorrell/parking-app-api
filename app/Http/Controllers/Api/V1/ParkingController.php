<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Parking\StartParkingRequest;
use App\Http\Resources\ParkingResource;
use App\Models\Parking;
use App\Services\ParkingPriceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

class ParkingController extends Controller
{
    public function start(StartParkingRequest $request): JsonResource | JsonResponse
    {
        $data = $request->validated();

        if (Parking::active()->where('vehicle_id', $data['vehicle_id'])->exists()) {
            return response()->json([
                'errors' => ['general' => ['This vehicle is already parked. Please, stop active parking and try again.']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $parking = Parking::create($data)->load('vehicle', 'zone');

        return ParkingResource::make($parking);
    }

    public function show(Parking $parking): JsonResource
    {
        $parking->loadMissing('vehicle', 'zone');

        return ParkingResource::make($parking);
    }

    public function stop(Parking $parking, ParkingPriceService $parkingPriceService): JsonResource | JsonResponse
    {
        if ($parking->stop_time) {
            return response()->json([
                'errors' => ['general' => ['Whoops, seems like this parking has already been stopped.']]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $parking->update([
            'stop_time' => now(),
            'total_price' => $parkingPriceService->calculateTotalPrice($parking->zone, $parking->start_time)
        ]);

        return ParkingResource::make($parking);
    }
}