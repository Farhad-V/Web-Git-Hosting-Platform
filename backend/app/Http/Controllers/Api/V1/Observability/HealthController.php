<?php

namespace App\Http\Controllers\Api\V1\Observability;

use App\Http\Controllers\Controller;
use App\Services\Observability\HealthService;
use Illuminate\Http\JsonResponse;

class HealthController extends Controller
{
    public function readiness(
        HealthService $health,
    ): JsonResponse {
        $result = $health->readiness();

        /*
         * Public health responses intentionally remain minimal.
         * Dependency-specific status stays internal to the service and logs.
         */
        return response()->json(
            [
                'status' => $result['ready']
                    ? 'ok'
                    : 'unavailable',
            ],
            $result['ready']
                ? 200
                : 503,
        );
    }
}