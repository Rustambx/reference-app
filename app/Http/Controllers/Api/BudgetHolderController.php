<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Http\Requests\BudgetHolder\BudgetHolderStoreRequest;
use App\Http\Requests\BudgetHolder\BudgetHolderUpdateRequest;
use App\Http\Resources\BudgetHolderResource;
use App\Services\BudgetHolderService;
use Illuminate\Http\Request;

class BudgetHolderController extends Controller
{
    private BudgetHolderService $budgetHolderService;

    public function __construct(BudgetHolderService $budgetHolderService)
    {
        $this->budgetHolderService = $budgetHolderService;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $budgetHolders = $this->budgetHolderService->getPaginated($request);

        return ApiResponse::paginated($budgetHolders, BudgetHolderResource::class, "Записи успешно получены");
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(BudgetHolderStoreRequest $request)
    {
        $data = $request->validated();

        $budgetHolder = $this->budgetHolderService->create($data, $request->user()->id);

        return ApiResponse::success(BudgetHolderResource::make($budgetHolder), "Запись успешно добавлена");
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $budgetHolder = $this->budgetHolderService->getById($id);

        return ApiResponse::success(BudgetHolderResource::make($budgetHolder), "Запись успешно получена");
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(BudgetHolderUpdateRequest $request, string $id)
    {
        $data = $request->validated();
        $budgetHolder = $this->budgetHolderService->getById($id);
        $budgetHolderUpdated = $this->budgetHolderService->update($budgetHolder, $data, $request->user()->id);

        return ApiResponse::success(BudgetHolderResource::make($budgetHolderUpdated), "Запись успешно обновлена");
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $budgetHolder = $this->budgetHolderService->getById($id);

        $this->budgetHolderService->delete($budgetHolder);

        return ApiResponse::success([], "Запись успешно удалена");
    }
}
