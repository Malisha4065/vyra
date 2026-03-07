<?php

namespace Tests;

use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests should not depend on compiled frontend assets.
        $this->withoutVite();

        // Most feature tests exercise domain behavior, not email-verification gatekeeping.
        $this->withoutMiddleware(EnsureEmailIsVerified::class);
    }
}
