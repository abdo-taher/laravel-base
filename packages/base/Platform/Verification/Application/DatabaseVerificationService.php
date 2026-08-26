<?php

declare(strict_types=1);

namespace Base\Platform\Verification\Application;

use Base\Platform\Verification\Public\Contracts\VerificationIssuer;
use Base\Platform\Verification\Public\Contracts\VerificationVerifier;
use Base\Platform\Verification\Public\Exceptions\VerificationAttemptsExceeded;
use Base\Platform\Verification\Public\Exceptions\VerificationConsumed;
use Base\Platform\Verification\Public\Exceptions\VerificationExpired;
use Base\Platform\Verification\Public\Exceptions\VerificationInvalid;
use Base\Platform\Verification\Public\Exceptions\VerificationNotFound;
use Base\Platform\Verification\Public\ValueObjects\IssuedChallenge;
use Base\Platform\Verification\Public\ValueObjects\VerificationPurpose;
use Base\Platform\Verification\Public\ValueObjects\VerificationReference;
use Base\Platform\Verification\Public\ValueObjects\VerificationTarget;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

final class DatabaseVerificationService implements VerificationIssuer, VerificationVerifier
{
    public function issue(
        VerificationTarget $target,
        VerificationPurpose $purpose,
        int $length = 6,
        int $ttlSeconds = 900,
        int $maxAttempts = 3
    ): IssuedChallenge {
        if ($length < 4 || $length > 12) {
            throw new \InvalidArgumentException('Invalid length');
        }
        if ($ttlSeconds < 1) {
            throw new \InvalidArgumentException('Invalid TTL');
        }
        if ($maxAttempts < 1) {
            throw new \InvalidArgumentException('Invalid max attempts');
        }

        return DB::transaction(function () use ($target, $purpose, $length, $ttlSeconds, $maxAttempts) {
            $now = Carbon::now();

            DB::table('verification_challenges')
                ->where('target_type', $target->type)
                ->where('target_key', $target->key)
                ->where('purpose', $purpose->value)
                ->where('is_active', true)
                ->where('expires_at', '>', $now)
                ->whereColumn('attempts', '<', 'max_attempts')
                ->lockForUpdate()
                ->update(['invalidated_at' => $now, 'is_active' => null]);

            $reference = VerificationReference::generate();
            $plaintextCode = $this->generateNumericCode($length);
            $codeHash = Hash::make($plaintextCode);

            DB::table('verification_challenges')->insert([
                'id' => (string) Str::uuid(),
                'reference' => $reference->value,
                'target_type' => $target->type,
                'target_key' => $target->key,
                'purpose' => $purpose->value,
                'code_hash' => $codeHash,
                'attempts' => 0,
                'max_attempts' => $maxAttempts,
                'expires_at' => $now->copy()->addSeconds($ttlSeconds),
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            return new IssuedChallenge($reference, $plaintextCode);
        });
    }

    public function verify(VerificationReference $reference, string $code): void
    {
        $exception = null;
        DB::transaction(function () use ($reference, $code, &$exception) {
            $challenge = DB::table('verification_challenges')
                ->where('reference', $reference->value)
                ->lockForUpdate()
                ->first();

            if (! $challenge) {
                $exception = new VerificationNotFound;

                return;
            }

            if ($challenge->invalidated_at !== null) {
                $exception = new VerificationNotFound;

                return;
            }

            if ($challenge->consumed_at !== null) {
                $exception = new VerificationConsumed;

                return;
            }

            if (Carbon::parse($challenge->expires_at)->isPast()) {
                $exception = new VerificationExpired;

                return;
            }

            if ($challenge->attempts >= $challenge->max_attempts) {
                $exception = new VerificationAttemptsExceeded;

                return;
            }

            if (! Hash::check($code, $challenge->code_hash)) {
                DB::table('verification_challenges')
                    ->where('id', $challenge->id)
                    ->update([
                        'attempts' => $challenge->attempts + 1,
                        'updated_at' => Carbon::now(),
                    ]);

                if ($challenge->attempts + 1 >= $challenge->max_attempts) {
                    $exception = new VerificationAttemptsExceeded;

                    return;
                }

                $exception = new VerificationInvalid;

                return;
            }

            DB::table('verification_challenges')
                ->where('id', $challenge->id)
                ->update([
                    'consumed_at' => Carbon::now(),
                    'is_active' => null,
                    'updated_at' => Carbon::now(),
                ]);
        });

        if ($exception) {
            throw $exception;
        }
    }

    private function generateNumericCode(int $length): string
    {
        $min = 10 ** ($length - 1);
        $max = (10 ** $length) - 1;

        return (string) random_int($min, $max);
    }
}
