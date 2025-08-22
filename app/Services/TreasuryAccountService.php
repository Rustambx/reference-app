<?php

namespace App\Services;

use App\Models\TreasuryAccount;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class TreasuryAccountService
{
    public function getPaginated(Request $request): LengthAwarePaginator
    {
        $perPage = (int) $request->input('per_page', 20);
        $sort = $request->input('sort', 'name');
        $direction = $request->input('direction', 'asc');
        $search = trim((string) $request->input('search', ''));

        $query = TreasuryAccount::query()
            ->when($request->filled('account'), fn($q) =>
                $q->where('account', 'ILIKE', '%' . $request->input('account') . '%'))
            ->when($request->filled('name'), fn($q) =>
                $q->where('name', 'ILIKE', '%' . $request->input('name')->trim() . '%'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('account', 'ILIKE', '%' . $search . '%')
                    ->orWhere('name', 'ILIKE', '%' . $search . '%')
                    ->orWhere('mfo', 'ILIKE', '%' . $search . '%')
                    ->orWhere('currency', 'ILIKE', '%' . $search . '%');
            });

            $rankSql = "GREATEST(
                similarity(account, ?),
                similarity(name, ?),
                similarity(mfo, ?),
                similarity(currency, ?),
            )";

            $query->select('*')->selectRaw("$rankSql AS rank", [$search, $search, $search, $search])
                ->orderByDesc('rank')
                ->orderBy($sort, $direction)
                ->orderBy('id');
        } else {
            $query->orderBy($sort, $direction)->orderBy('id');
        }

        return  $query->paginate($perPage)->withQueryString();
    }

    public function getById(string $id): TreasuryAccount
    {
        return TreasuryAccount::findOrFail($id);
    }

    public function create(array $data, $userId): TreasuryAccount
    {
        return TreasuryAccount::create([
            'account' => $data['account'],
            'mfo' => $data['mfo'],
            'name' => $data['name'],
            'department' => $data['department'],
            'currency' => $data['currency'],
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(TreasuryAccount $account, array $data, $userId): TreasuryAccount
    {
        $account->update([
            'account' => $data['account'],
            'mfo' => $data['mfo'],
            'name' => $data['name'],
            'department' => $data['department'],
            'currency' => $data['currency'],
            'updated_by' => $userId,
        ]);

        return $account;
    }

    public function delete(TreasuryAccount $account)
    {
        $account->delete();
    }
}
