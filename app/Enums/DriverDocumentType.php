<?php

namespace App\Enums;

enum DriverDocumentType: string
{
    case GovernmentId = 'government_id';
    case DriverLicense = 'driver_license';
    case Selfie = 'selfie';
    case ProofOfAddress = 'proof_of_address';
    case VehicleRegistration = 'vehicle_registration';
    case Insurance = 'insurance';
    case Roadworthiness = 'roadworthiness';
    case VehiclePhotoExterior = 'vehicle_photo_exterior';
    case VehiclePhotoInterior = 'vehicle_photo_interior';

    public static function requiredTypes(): array
    {
        return [
            self::GovernmentId->value,
            self::DriverLicense->value,
            self::Selfie->value,
            self::ProofOfAddress->value,
            self::VehicleRegistration->value,
            self::Insurance->value,
            self::Roadworthiness->value,
            self::VehiclePhotoExterior->value,
            self::VehiclePhotoInterior->value,
        ];
    }
}
