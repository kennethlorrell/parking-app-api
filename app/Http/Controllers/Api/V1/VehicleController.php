<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vehicle\StoreVehicleRequest;
use App\Http\Requests\Vehicle\UpdateVehicleRequest;
use App\Http\Resources\VehicleResource;
use App\Models\Vehicle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;

/**
 * @group Vehicles
 */
class VehicleController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $vehicles = Vehicle::all();

        return VehicleResource::collection($vehicles);
    }

    public function store(StoreVehicleRequest $request): JsonResource
    {
        $vehicle = Vehicle::create($request->validated());

        return VehicleResource::make($vehicle);
    }

    public function show(Vehicle $vehicle): JsonResource
    {
        return VehicleResource::make($vehicle);
    }

    public function update(UpdateVehicleRequest $request, Vehicle $vehicle): JsonResponse
    {
        if ($vehicle->hasActiveParkings()) {
            return response()->json([
                'errors' => ['general' => [__('messages.can_not_update_vehicle_with_active_parkings')]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $vehicle->update($request->validated());

        return response()->json(
            VehicleResource::make($vehicle),
            Response::HTTP_ACCEPTED
        );
    }

    public function destroy(Vehicle $vehicle): Response | JsonResponse
    {
        if ($vehicle->hasActiveParkings()) {
            return response()->json([
                'errors' => ['general' => [__('messages.can_not_remove_vehicle_with_active_parkings')]]
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $vehicle->delete();

        return response()->noContent();
    }
}
