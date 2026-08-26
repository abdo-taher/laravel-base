<?php

declare(strict_types=1);

namespace Tests\Feature\Packages\Base\Platform\Verification;

use Base\Platform\Verification\Application\DatabaseVerificationService;
use Base\Platform\Verification\Public\Exceptions\VerificationAttemptsExceeded;
use Base\Platform\Verification\Public\Exceptions\VerificationConsumed;
use Base\Platform\Verification\Public\Exceptions\VerificationExpired;
use Base\Platform\Verification\Public\Exceptions\VerificationInvalid;
use Base\Platform\Verification\Public\Exceptions\VerificationNotFound;
use Base\Platform\Verification\Public\ValueObjects\VerificationPurpose;
use Base\Platform\Verification\Public\ValueObjects\VerificationTarget;
use Base\Platform\Verification\VerificationServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseVerificationServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders(mixed $app): array
    {
        return [VerificationServiceProvider::class];
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->app->register(VerificationServiceProvider::class);
        $this->artisan('migrate');
    }

    public function test_issue_stores_only_hash(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose);

        $this->assertNotNull($dbRecord = DB::table('verification_challenges')->where('reference', $challenge->reference->value)->first());

        $this->assertNotSame($challenge->plaintextCode, $dbRecord->code_hash);
        $this->assertTrue(password_verify($challenge->plaintextCode, $dbRecord->code_hash));
    }

    public function test_successful_verify_consumes(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose);
        $service->verify($challenge->reference, $challenge->plaintextCode);

        $this->assertNotNull($dbRecord = DB::table('verification_challenges')->where('reference', $challenge->reference->value)->first());
        $this->assertNotNull($dbRecord->consumed_at);
        $this->assertNull($dbRecord->is_active);
    }

    public function test_invalid_code_increments_attempts(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose);

        $this->expectException(VerificationInvalid::class);
        $service->verify($challenge->reference, 'wrong');

        $this->assertNotNull($dbRecord = DB::table('verification_challenges')->where('reference', $challenge->reference->value)->first());
        $this->assertSame(1, $dbRecord->attempts);
    }

    public function test_limit_locks_challenge(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose, 6, 900, 2);

        try {
            $service->verify($challenge->reference, 'wrong1');
        } catch (VerificationInvalid $e) {
        }

        try {
            $service->verify($challenge->reference, 'wrong2');
        } catch (VerificationAttemptsExceeded $e) {
        }

        $this->assertNotNull($dbRecord = DB::table('verification_challenges')->where('reference', $challenge->reference->value)->first());
        $this->assertSame(2, $dbRecord->attempts);

        $this->expectException(VerificationAttemptsExceeded::class);
        $service->verify($challenge->reference, 'wrong3');
    }

    public function test_expired_rejected(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose);

        Carbon::setTestNow(Carbon::now()->addMinutes(30));

        $this->expectException(VerificationExpired::class);
        $service->verify($challenge->reference, $challenge->plaintextCode);
    }

    public function test_consumed_replay_rejected(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge = $service->issue($target, $purpose);
        $service->verify($challenge->reference, $challenge->plaintextCode);

        $this->expectException(VerificationConsumed::class);
        $service->verify($challenge->reference, $challenge->plaintextCode);
    }

    public function test_issue_again_invalidates_old(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target = new VerificationTarget('email', 'test@test.com');
        $purpose = new VerificationPurpose('login');

        $challenge1 = $service->issue($target, $purpose);
        $challenge2 = $service->issue($target, $purpose);

        $this->assertNotNull($dbRecord1 = DB::table('verification_challenges')->where('reference', $challenge1->reference->value)->first());
        $this->assertNotNull($dbRecord1->invalidated_at);
        $this->assertNull($dbRecord1->is_active);

        $this->expectException(VerificationNotFound::class);
        $service->verify($challenge1->reference, $challenge1->plaintextCode);
    }

    public function test_issue_again_invalidates_old_but_not_different_target_or_purpose(): void
    {
        $service = $this->app->make(DatabaseVerificationService::class);
        $target1 = new VerificationTarget('email', 't1@t.com');
        $purpose1 = new VerificationPurpose('login');

        $target2 = new VerificationTarget('email', 't2@t.com');
        $purpose2 = new VerificationPurpose('other');

        $challengeA = $service->issue($target1, $purpose1);
        $challengeDiffTarget = $service->issue($target2, $purpose1);
        $challengeDiffPurpose = $service->issue($target1, $purpose2);

        $challengeB = $service->issue($target1, $purpose1);

        $this->assertNotNull($dbA = DB::table('verification_challenges')->where('reference', $challengeA->reference->value)->first());
        $this->assertNotNull($dbB = DB::table('verification_challenges')->where('reference', $challengeB->reference->value)->first());
        $this->assertNotNull($dbDiffTarget = DB::table('verification_challenges')->where('reference', $challengeDiffTarget->reference->value)->first());
        $this->assertNotNull($dbDiffPurpose = DB::table('verification_challenges')->where('reference', $challengeDiffPurpose->reference->value)->first());

        $this->assertNotNull($dbA->invalidated_at);
        $this->assertNull($dbA->is_active);

        $this->assertNull($dbB->invalidated_at);
        $this->assertSame(1, $dbB->is_active);

        $this->assertNull($dbDiffTarget->invalidated_at);
        $this->assertSame(1, $dbDiffTarget->is_active);

        $this->assertNull($dbDiffPurpose->invalidated_at);
        $this->assertSame(1, $dbDiffPurpose->is_active);
    }
}
