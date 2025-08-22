<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\TreasuryAccount\TreasuryAccountImportRequest;
use App\Http\Requests\TreasuryAccount\TreasuryAccountStoreRequest;
use App\Http\Requests\TreasuryAccount\TreasuryAccountUpdateRequest;
use App\Http\Resources\TreasuryAccountResource;
use App\Imports\TreasuryAccountImport;
use App\Services\TreasuryAccountService;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

class TreasuryAccountController extends Controller
{
    private TreasuryAccountService $service;

    public function __construct(TreasuryAccountService $service)
    {
        $this->service = $service;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $accounts = $this->service->getPaginated($request);

        return ApiResponse::paginated($accounts, TreasuryAccountResource::class, "Записи успешно получены");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(TreasuryAccountStoreRequest $request)
    {
        $data = $request->validated();
        $treasuryAccount = $this->service->create($data, $request->user()->id);

        return ApiResponse::success(TreasuryAccountResource::make($treasuryAccount), "Запись успешно добавлена");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $treasuryAccount = $this->service->getById($id);

        return ApiResponse::success(TreasuryAccountResource::make($treasuryAccount), "Запись успешно получена");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(TreasuryAccountUpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $treasuryAccount = $this->service->getById($id);
        $treasuryAccountUpdated = $this->service->update($treasuryAccount, $data ,$request->user()->id);

        return ApiResponse::success(TreasuryAccountResource::make($treasuryAccountUpdated), "Запись успешно обновлена");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $treasuryAccount = $this->service->getById($id);
        $this->service->delete($treasuryAccount);

        return ApiResponse::success([], "Запись успешно удалена");
    }

    public function import(TreasuryAccountImportRequest $request)
    {
        $file = $request->file('file');

        Excel::queueImport(new TreasuryAccountImport($request->user()->id), $file)
            ->allOnConnection('rabbitmq')
            ->allOnQueue('treasury_account');

        return ApiResponse::success([], "Импорт запущен");
    }
}
