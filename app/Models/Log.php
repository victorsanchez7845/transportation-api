<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Log extends Model {
    protected $fillable = [
        'reservation_id',
        'process_id',
        'type',
        'category',
        'message',
        'exception',
    ];
}
