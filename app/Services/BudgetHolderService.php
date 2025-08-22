<?php

namespace App\Services;

use App\Models\BudgetHolder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class BudgetHolderService
{
    public function getPaginated(Request $request): LengthAwarePaginator
    {
        $perPage = (int)$request->input('per_page', 20);
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $search = trim((string)$request->input('search', ''));

        $query = BudgetHolder::query()
            ->when($request->filled('tin'), fn($q) => $q->where('tin', strtoupper($request->string('tin')->trim())))
            ->when($request->filled('name'), fn($q) => $q->where('name', 'ILIKE', '%' . $request->string('name')->trim() . '%'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('tin', 'ILIKE', '%' . $search . '%')
                    ->orWhere('name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('region', 'ILIKE', '%' . $search . '%')
                    ->orWhere('phone', 'ILIKE', '%' . $search . '%');
            });

            $rankSql = "GREATEST(
            similarity(tin, ?),
            similarity(name, ?),
            similarity(region, ?),
            similarity(phone, ?)
        )";

            $query->select('*')->selectRaw("$rankSql AS rank", [$search, $search, $search, $search])
                ->orderByDesc('rank')
                ->orderBy($sort, $direction)
                ->orderBy('id');
        } else {
            $query->orderBy($sort, $direction)->orderBy('id');
        }

        return $query->paginate($perPage)->withQueryString();
    }

    public function getById(string $id): BudgetHolder
    {
        return BudgetHolder::findOrFail($id);
    }

    public function create(array $data, $userId): BudgetHolder
    {
        return BudgetHolder::create([
            'tin' => $data['tin'],
            'name' => $data['name'],
            'region' => $data['region'],
            'district' => $data['district'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'responsible' => $data['responsible'],
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(BudgetHolder $budgetHolder, array $data, $userId): BudgetHolder
    {
        $budgetHolder->update([
            'tin' => $data['tin'],
            'name' => $data['name'],
            'region' => $data['region'],
            'district' => $data['district'],
            'address' => $data['address'],
            'phone' => $data['phone'],
            'responsible' => $data['responsible'],
            'updated_by' => $userId,
        ]);

        return $budgetHolder;
    }

    public function delete(BudgetHolder $budgetHolder)
    {
        $budgetHolder->delete();
    }
}
