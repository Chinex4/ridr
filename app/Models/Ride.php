<?php

namespace App\Models;

use App\Enums\CancelBy;
use App\Enums\RideStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Ride extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $fillable = [
        'rider_id',
        'driver_id',
        'status',
        'pickup_lat',
        'pickup_lng',
        'pickup_address',
        'dropoff_lat',
        'dropoff_lng',
        'dropoff_address',
        'estimated_distance_km',
        'estimated_duration_min',
        'estimated_fare_amount',
        'final_fare_amount',
        'currency',
        'requested_at',
        'accepted_at',
        'arrived_at',
        'started_at',
        'completed_at',
        'cancelled_at',
        'cancel_by',
        'cancel_reason',
        'driver_last_lat',
        'driver_last_lng',
    ];

    protected $casts = [
        'status' => RideStatus::class,
        'pickup_lat' => 'decimal:7',
        'pickup_lng' => 'decimal:7',
        'dropoff_lat' => 'decimal:7',
        'dropoff_lng' => 'decimal:7',
        'estimated_distance_km' => 'decimal:2',
        'estimated_duration_min' => 'integer',
        'requested_at' => 'datetime',
        'accepted_at' => 'datetime',
        'arrived_at' => 'datetime',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'cancel_by' => CancelBy::class,
        'driver_last_lat' => 'decimal:7',
        'driver_last_lng' => 'decimal:7',
    ];

    public function rider()
    {
        return $this->belongsTo(User::class, 'rider_id');
    }

    public function driver()
    {
        return $this->belongsTo(User::class, 'driver_id');
    }

    public function events()
    {
        return $this->hasMany(RideEvent::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
