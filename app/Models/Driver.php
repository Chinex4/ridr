<?php

namespace App\Models;

use App\Enums\KycStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class Driver extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'kyc_status',
        'kyc_submitted_at',
        'kyc_reviewed_at',
        'kyc_rejection_reason',
        'license_number',
        'license_expiry',
        'vehicle_make',
        'vehicle_model',
        'vehicle_plate',
        'is_online',
        'current_lat',
        'current_lng',
        'last_location_at',
        'rating_average',
        'rating_count',
    ];

    protected $casts = [
        'kyc_status' => KycStatus::class,
        'kyc_submitted_at' => 'datetime',
        'kyc_reviewed_at' => 'datetime',
        'license_expiry' => 'date',
        'is_online' => 'boolean',
        'current_lat' => 'decimal:7',
        'current_lng' => 'decimal:7',
        'last_location_at' => 'datetime',
        'rating_average' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function documents()
    {
        return $this->hasMany(DriverDocument::class);
    }
}
