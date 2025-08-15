<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'license_number',
        'license_expiry',
        'date_of_birth',
        'hire_date',
        'status',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
    ];

    protected $casts = [
        'license_expiry' => 'date',
        'date_of_birth' => 'date',
        'hire_date' => 'date',
    ];

    // Relationships
    public function trips(): HasMany
    {
        return $this->hasMany(Trip::class);
    }

    // Accessors
    public function getFullNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
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
