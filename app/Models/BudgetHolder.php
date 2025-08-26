<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BudgetHolder extends Model
{
    protected $fillable = [
        'id',
        'tin',
        'name',
        'region',
        'district',
        'address',
        'phone',
        'responsible',
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
}
