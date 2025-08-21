<?php

namespace App\Services;

use App\Models\Swift;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class SwiftService
{
    public function getPaginated(Request $request): LengthAwarePaginator
    {
        $perPage   = (int) $request->input('per_page', 20);
        $sort      = $request->input('sort', 'bank_name');
        $direction = $request->input('direction', 'asc');
        $search    = trim((string) $request->input('search', ''));

        $query = Swift::query()
            ->when($request->filled('country'), fn($q) =>
            $q->where('country', strtoupper($request->string('country')->trim())))
            ->when($request->filled('city'), fn($q) =>
            $q->where('city', 'ILIKE', '%'.$request->string('city')->trim().'%'))
            ->when($request->filled('swift_code'), fn($q) =>
            $q->where('swift_code', 'ILIKE', '%'.$request->string('swift_code')->trim().'%'))
            ->when($request->filled('bank_name'), fn($q) =>
            $q->where('bank_name', 'ILIKE', '%'.$request->string('bank_name')->trim().'%'));

        if ($search !== '') {
            $query->where(function ($q) use ($search) {
                $q->where('bank_name',  'ILIKE', '%'.$search.'%')
                    ->orWhere('swift_code','ILIKE', '%'.$search.'%')
                    ->orWhere('city',      'ILIKE', '%'.$search.'%')
                    ->orWhere('address',   'ILIKE', '%'.$search.'%');
            });

            $rankSql = "GREATEST(
            similarity(bank_name,  ?),
            similarity(swift_code, ?),
            similarity(city,       ?),
            similarity(address,    ?)
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

    public function getById(string $id): Swift
    {
        return Swift::findOrFail($id);
    }

    public function create(array $data, string $userId)
    {
        return Swift::create([
            'swift_code' => $data['swift_code'],
            'bank_name' => $data['bank_name'],
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'],
            'created_by' => $userId,
            'updated_by' => $userId,
        ]);
    }

    public function update(Swift $swift, array $data, string $userId)
    {
        $swift->update([
            'swift_code' => $data['swift_code'],
            'bank_name' => $data['bank_name'],
            'country' => $data['country'],
            'city' => $data['city'],
            'address' => $data['address'],
            'updated_by' => $userId,
        ]);

        return $swift;
    }

    public function delete(Swift $swift)
    {
        $swift->delete();
    }
}
