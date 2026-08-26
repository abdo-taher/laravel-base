<?php

declare(strict_types=1);

namespace Base\Platform\Devices\Application;

use Base\Foundation\Identity\Public\ValueObjects\PrincipalId;
use Base\Platform\Devices\Public\Contracts\DeviceRegistry;
use Base\Platform\Devices\Public\Exceptions\DeviceNotFound;
use Base\Platform\Devices\Public\ValueObjects\DeviceId;
use Base\Platform\Devices\Public\ValueObjects\DevicePlatform;
use Base\Platform\Devices\Public\ValueObjects\DeviceRegistration;
use Base\Platform\Devices\Public\ValueObjects\PushToken;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class DatabaseDeviceRegistry implements DeviceRegistry
{
    public function register(
        PrincipalId $owner,
        DeviceId $device,
        DevicePlatform $platform,
        ?PushToken $token = null,
        ?string $appVersion = null
    ): void {
        DB::transaction(function () use ($owner, $device, $platform, $token, $appVersion) {
            $now = Carbon::now();

            if ($token !== null) {
                DB::table('devices')
                    ->where('push_token', $token->value)
                    ->where('device_id', '!=', $device->value)
                    ->update([
                        'push_token' => null,
                        'updated_at' => $now,
                    ]);
            }

            $existing = DB::table('devices')
                ->where('device_id', $device->value)
                ->lockForUpdate()
                ->first();

            if ($existing) {
                DB::table('devices')
                    ->where('id', $existing->id)
                    ->update([
                        'principal_id' => $owner->value,
                        'platform' => $platform->value,
                        'push_token' => $token?->value,
                        'app_version' => $appVersion,
                        'active' => true,
                        'last_seen_at' => $now,
                        'updated_at' => $now,
                    ]);
            } else {
                DB::table('devices')->insert([
                    'id' => (string) Str::uuid(),
                    'device_id' => $device->value,
                    'principal_id' => $owner->value,
                    'platform' => $platform->value,
                    'push_token' => $token?->value,
                    'app_version' => $appVersion,
                    'active' => true,
                    'last_seen_at' => $now,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        });
    }

    public function unregister(DeviceId $device): void
    {
        DB::table('devices')
            ->where('device_id', $device->value)
            ->update([
                'active' => false,
                'push_token' => null,
                'updated_at' => Carbon::now(),
            ]);
    }

    public function touch(DeviceId $device): void
    {
        $updated = DB::table('devices')
            ->where('device_id', $device->value)
            ->update([
                'last_seen_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]);

        if ($updated === 0) {
            throw new DeviceNotFound;
        }
    }

    public function devicesForPrincipal(PrincipalId $owner): array
    {
        $rows = DB::table('devices')
            ->where('principal_id', $owner->value)
            ->where('active', true)
            ->get();

        return $rows->map(function ($row) {
            return new DeviceRegistration(
                new DeviceId($row->device_id),
                new PrincipalId($row->principal_id),
                new DevicePlatform($row->platform),
                $row->push_token ? new PushToken($row->push_token) : null,
                $row->app_version,
                (bool) $row->active
            );
        })->toArray();
    }
}
