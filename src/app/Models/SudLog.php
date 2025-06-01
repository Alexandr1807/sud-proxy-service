<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

// app/Models/SudLog.php
class SudLog extends Model
{
    public $timestamps = false;
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'endpoint',
        'request_data',
        'response_data',
        'status',
        'http_code',
        'error_message',
        'created_at',
    ];

    protected $casts = [
        'id'            => 'string',
        'user_id'       => 'string',
        'request_data'  => 'array',
        'response_data' => 'array',
        'http_code'     => 'integer',
        'created_at'    => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (! $model->id) {
                $model->id = (string) Str::uuid();
            }
        });
    }
}

