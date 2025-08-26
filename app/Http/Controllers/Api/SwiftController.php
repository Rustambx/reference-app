<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\Swift\SwiftImportRequest;
use App\Http\Requests\Swift\SwiftStoreRequest;
use App\Http\Requests\Swift\SwiftUpdateRequest;
use App\Http\Resources\SwiftResource;
use App\Imports\SwiftImport;
use App\Services\SwiftService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class SwiftController extends Controller
{
    private SwiftService $swiftService;

    public function __construct(SwiftService $swiftService)
    {
        $this->swiftService = $swiftService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $swifts = $this->swiftService->getPaginated($request);

        return ApiResponse::paginated($swifts, SwiftResource::class, "Записи успешно получены");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(SwiftStoreRequest $request)
    {
        $data = $request->validated();
        $swift = $this->swiftService->create($data, $request->user()->id);

        return ApiResponse::success(SwiftResource::make($swift), "Запись успешно добавлена");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $swift = $this->swiftService->getById($id);

        return ApiResponse::success(SwiftResource::make($swift), "Запись успешно получена");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(SwiftUpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $swift = $this->swiftService->getById($id);
        $swiftUpdated = $this->swiftService->update($swift, $data, $request->user()->id);

        return ApiResponse::success(SwiftResource::make($swiftUpdated), "Запись успешно обновлена");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $swift = $this->swiftService->getById($id);
        $this->swiftService->delete($swift);

        return ApiResponse::success([], "Запись успешно удалена");
    }

    public function import(SwiftImportRequest $request)
    {
        $file = $request->file('file');

        Excel::queueImport(new SwiftImport($request->user()->id), $file)
            ->allOnConnection('rabbitmq')
            ->allOnQueue('swift');

        return ApiResponse::success([], "Импорт запущен");
    }
}
