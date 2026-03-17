<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Clear Spatie cache to avoid issues with RefreshDatabase trait
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Seed Spatie roles before each test so new users can be correctly assigned roles
        $this->seed(\Database\Seeders\RoleAndPermissionSeeder::class);
    }
}
