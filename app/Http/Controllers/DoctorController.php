<?php

namespace App\Http\Controllers;

use App\DTOs\NetworkAggregatesDTO;
use App\Http\Requests\NetworkAggregatesRequest;
use App\Services\NetworkAggregationService;
use Illuminate\Http\JsonResponse;

class DoctorController extends Controller
{
    public function networkAggregates(
        int $id,
        NetworkAggregatesRequest $request,
        NetworkAggregationService $service,
    ): JsonResponse {

        $dto = new NetworkAggregatesDTO(
            doctorId: $id,
            specialization: $request->input('specialization'),
            minYoe: $request->input('min_yoe'),
            maxYoe: $request->input('max_yoe'),
        );

        $result = $service->getNetworkAggregates($dto);

        return response()->json($result);
    }
}
