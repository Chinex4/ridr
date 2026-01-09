<?php

namespace App\Services;

use App\Models\IdempotencyKey;
use App\Models\User;

class IdempotencyService
{
    public function findExisting(User $user, string $endpoint, string $key): ?IdempotencyKey
    {
        return IdempotencyKey::where('user_id', $user->id)
            ->where('endpoint', $endpoint)
            ->where('key', $key)
            ->where('expires_at', '>', now())
            ->first();
    }

    public function assertRequestHash(IdempotencyKey $record, array $payload): bool
    {
        if (! $record->request_hash) {
            return true;
        }

        return hash_equals($record->request_hash, $this->hashPayload($payload));
    }

    public function create(User $user, string $endpoint, string $key, array $payload, int $ttlMinutes = 10): IdempotencyKey
    {
        return IdempotencyKey::create([
            'user_id' => $user->id,
            'key' => $key,
            'endpoint' => $endpoint,
            'request_hash' => $this->hashPayload($payload),
            'expires_at' => now()->addMinutes($ttlMinutes),
        ]);
    }

    public function storeResponse(IdempotencyKey $record, int $status, array $body): void
    {
        $record->update([
            'response_code' => $status,
            'response_body' => $body,
        ]);
    }

    private function hashPayload(array $payload): string
    {
        return hash('sha256', json_encode($payload));
    }
}
