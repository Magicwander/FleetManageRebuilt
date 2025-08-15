<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'fuel_date',
        'fuel_type',
        'quantity',
        'cost_per_liter',
        'total_cost',
        'mileage',
        'location',
        'receipt_number',
        'notes',
    ];

    protected $casts = [
        'fuel_date' => 'date',
        'quantity' => 'decimal:2',
        'cost_per_liter' => 'decimal:2',
        'total_cost' => 'decimal:2',
        'mileage' => 'integer',
    ];

    // Relationships
    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    // Accessors
    public function getFuelEfficiencyAttribute(): ?float
    {
        if ($this->quantity > 0) {
            // This would need trip distance data to calculate properly
            return null; // Placeholder for fuel efficiency calculation
        }
        return null;
    }
}
