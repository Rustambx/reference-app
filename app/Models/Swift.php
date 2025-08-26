<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Swift extends Model
{
    protected $fillable = [
        'id',
        'swift_code',
        'bank_name',
        'country',
        'city',
        'address',
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

    protected function swiftCode(): Attribute
    {
        return Attribute::make(
            set: fn($v) => strtoupper(trim((string)$v))
        );
    }

    protected function country(): Attribute
    {
        return Attribute::make(
            set: fn($v) => strtoupper(trim((string)$v))
        );
    }
}
