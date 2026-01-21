<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OpenpayClient extends Model
{
    use HasFactory;
    protected $table = 'openpay_clients';
    protected $fillable = [
        'client_name',
        'client_email',
        'client_openpay_id',
        'client_data',
    ];

    protected $casts = [
        'client_data' => 'array',
    ];
}
