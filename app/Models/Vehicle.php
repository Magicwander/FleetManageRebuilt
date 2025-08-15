<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'license_plate',
        'make',
        'model',
        'year',
        'vin',
        'color',
        'fuel_type',
        'capacity',
        'mileage',
        'status',
        'purchase_date',
        'purchase_price',
        'insurance_expiry',
        'registration_expiry',
    ];

    protected $casts = [
        'purchase_date' => 'date',
        'insurance_expiry' => 'date',
        'registration_expiry' => 'date',
        'purchase_price' => 'decimal:2',
        'mileage' => 'integer',
        'capacity' => 'decimal:2',
    ];

    // Relationships
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    public function maintenances(): HasMany
    {
        return $this->hasMany(Maintenance::class);
    }

    public function fuelRecords(): HasMany
    {
        return $this->hasMany(FuelRecord::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
