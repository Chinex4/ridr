<?php

namespace App\Services;

use App\Enums\DriverDocumentStatus;
use App\Enums\KycStatus;
use App\Models\Driver;
use App\Models\DriverDocument;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class KycService
{
    public function submit(User $user, array $data): Driver
    {
        return DB::transaction(function () use ($user, $data) {
            $driver = Driver::firstOrCreate(
                ['user_id' => $user->id],
                ['kyc_status' => KycStatus::Pending]
            );

            $driver->update([
                'kyc_status' => KycStatus::Pending,
                'kyc_submitted_at' => now(),
                'license_number' => $data['license_number'] ?? $driver->license_number,
                'license_expiry' => $data['license_expiry'] ?? $driver->license_expiry,
                'vehicle_make' => $data['vehicle_make'] ?? $driver->vehicle_make,
                'vehicle_model' => $data['vehicle_model'] ?? $driver->vehicle_model,
                'vehicle_plate' => $data['vehicle_plate'] ?? $driver->vehicle_plate,
            ]);

            foreach ($data['documents'] as $document) {
                DriverDocument::create([
                    'driver_id' => $driver->id,
                    'type' => $document['type'],
                    'file_path' => $document['file_path'],
                    'status' => DriverDocumentStatus::Pending,
                ]);
            }

            return $driver->fresh('documents');
        });
    }

    public function review(Driver $driver, bool $approve, ?string $reason = null): Driver
    {
        return DB::transaction(function () use ($driver, $approve, $reason) {
            $status = $approve ? KycStatus::Approved : KycStatus::Rejected;

            $driver->update([
                'kyc_status' => $status,
                'kyc_reviewed_at' => now(),
                'kyc_rejection_reason' => $approve ? null : $reason,
            ]);

            $driver->documents()->update([
                'status' => $approve ? DriverDocumentStatus::Approved : DriverDocumentStatus::Rejected,
                'reviewed_at' => now(),
                'rejection_reason' => $approve ? null : $reason,
            ]);

            return $driver->fresh('documents');
        });
    }
}
