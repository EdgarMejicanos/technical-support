<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    protected $fillable = [
        'title',
        'description',
        'notes',
        'type',
        'status',
        'created_by',
        'assigned_to',

        // Pagos
        'total_amount',
        'paid_amount',
        'payment_status',
    ];

    use HasFactory;


    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function getPendingAmountAttribute(): float
    {
        return max(0, $this->total_amount - $this->paid_amount);
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->pending_amount <= 0;
    }


    protected static function booted()
    {
        static::saving(function ($ticket) {
            $ticket->total_amount ??= 0;
            $ticket->paid_amount ??= 0;

            // Estado de pago automático
            $ticket->payment_status =
                ($ticket->total_amount - $ticket->paid_amount) <= 0
                    ? 'paid'
                    : 'pending';
        });
    }

}
