<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class TreasuryAccount extends Model
{
    protected $fillable = [
        'id',
        'account',
        'mfo',
        'name',
        'department',
        'currency',
        'created_by',
        'updated_by',
    ];

    protected $keyType = 'string';
    public $incrementing = false;

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    protected function account(): Attribute
    {
        return Attribute::make(
            set: fn($v) => strtoupper(trim((string)$v))
        );
    }

    protected function currency(): Attribute
    {
        return Attribute::make(
            set: fn($v) => strtoupper(trim((string)$v))
        );
    }
}
