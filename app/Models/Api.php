<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Api extends Model
{
    use HasFactory;
    protected $table = "api";

    public function intents(){
        return $this->hasMany(ApiIntent::class, 'api_id')->whereBetween('api_intent.created_at', [date("Y-m-d").' 00:00:00', date("Y-m-d").' 23:59:59']);        
    }
}
