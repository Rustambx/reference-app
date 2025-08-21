<?php

namespace App\Helpers;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ApiResponse
{
    public static function success($data = [], string $message = 'Успешно', int $status = 200): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'success' => true,
        ], $status);
    }

    public static function error(array $data = [], string $message = 'Ошибка', int $status = 400): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'data' => $data,
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'success' => false,
        ], $status);
    }

    public static function paginated(LengthAwarePaginator $paginator, string $resourceClass, string $message)
    {
        $resource  = $resourceClass::collection($paginator);
        $payload   = $resource->response()->getData(true);

        return response()->json([
            'message'   => $message,
            'data'      => $payload['data'],
            'links'     => $payload['links'],
            'meta'      => $payload['meta'],
            'timestamp' => Carbon::now('UTC')->format('Y-m-d\TH:i:s.u\Z'),
            'success'   => true,
        ]);
    }
}
