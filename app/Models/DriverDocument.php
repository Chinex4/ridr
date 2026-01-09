<?php

namespace App\Models;

use App\Enums\DriverDocumentStatus;
use App\Enums\DriverDocumentType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class DriverDocument extends Model
{
    use HasUuids;

    protected $fillable = [
        'driver_id',
        'type',
        'file_path',
        'status',
        'reviewed_at',
        'rejection_reason',
    ];

    protected $casts = [
        'type' => DriverDocumentType::class,
        'status' => DriverDocumentStatus::class,
        'reviewed_at' => 'datetime',
    ];

    public function driver()
    {
        return $this->belongsTo(Driver::class);
    }
}
