<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenpayTransaction extends Model
{
    use HasFactory;
    protected $table = 'openpay_transactions';
    protected $fillable = [
      'openpay_transaction_id',
      'reservation_uuid',
      'status'
    ];

    protected $with = ['reservation'];

    /**
     * Get the reservation associated with the transaction
     * 
     * @return BelongsTo
     */
    public function reservation(): BelongsTo
    {
      return $this->belongsTo(Reservations::class, 'reservation_uuid', 'uuid');
    }
}