<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Modules\Authorization\Models\Permission;

abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withHeader('Accept-Language', 'pt');
    }

    /**
     * Create the permissions checked by the admin sidebar @can directives.
     * Call this in setUp() of any web test that renders the admin layout.
     */
    protected function createSidebarPermissions(): void
    {
        foreach (['user.list', 'role.list', 'permission.list', 'log.list'] as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }
    }
}
