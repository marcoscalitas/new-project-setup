<?php

namespace Tests\Feature\Settings;

use Illuminate\Support\Facades\Cache;
use Modules\Settings\Models\Setting;
use Modules\Settings\Services\SettingsService;
use Tests\TestCase;

class SettingsServiceTest extends TestCase
{
    private SettingsService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new SettingsService();
    }

    public function test_get_returns_null_when_key_does_not_exist(): void
    {
        $this->assertNull($this->service->get('nonexistent'));
    }

    public function test_get_returns_default_when_key_does_not_exist(): void
    {
        $result = $this->service->get('nonexistent', 'fallback');

        $this->assertSame('fallback', $result);
    }

    public function test_set_stores_value_in_database(): void
    {
        $this->service->set('site_name', 'My App');

        $this->assertDatabaseHas('settings', ['key' => 'site_name']);
    }

    public function test_get_returns_value_after_set(): void
    {
        $this->service->set('site_name', 'My App');

        $this->assertSame('My App', $this->service->get('site_name'));
    }

    public function test_set_overwrites_existing_value(): void
    {
        $this->service->set('site_name', 'Old Name');
        $this->service->set('site_name', 'New Name');

        $this->assertSame('New Name', $this->service->get('site_name'));
        $this->assertSame(1, Setting::where('key', 'site_name')->count());
    }

    public function test_set_stores_array_value(): void
    {
        $this->service->set('features', ['dark_mode' => true, 'beta' => false]);

        $result = $this->service->get('features');

        $this->assertSame(['dark_mode' => true, 'beta' => false], $result);
    }

    public function test_forget_removes_key_from_database(): void
    {
        $this->service->set('tmp_key', 'tmp_value');
        $this->service->forget('tmp_key');

        $this->assertDatabaseMissing('settings', ['key' => 'tmp_key']);
    }

    public function test_get_returns_null_after_forget(): void
    {
        $this->service->set('tmp_key', 'tmp_value');
        $this->service->forget('tmp_key');

        $this->assertNull($this->service->get('tmp_key'));
    }

    public function test_all_returns_all_settings_as_array(): void
    {
        $this->service->set('key_one', 'value_one');
        $this->service->set('key_two', 'value_two');

        $all = $this->service->all();

        $this->assertArrayHasKey('key_one', $all);
        $this->assertArrayHasKey('key_two', $all);
        $this->assertSame('value_one', $all['key_one']);
        $this->assertSame('value_two', $all['key_two']);
    }

    public function test_all_returns_empty_array_when_no_settings(): void
    {
        $all = $this->service->all();

        $this->assertSame([], $all);
    }

    public function test_flush_clears_cache(): void
    {
        $this->service->set('cached_key', 'cached_value');
        $this->service->all(); // populate cache

        $this->service->flush();

        // Directly delete from DB to simulate stale cache scenario
        Setting::where('key', 'cached_key')->delete();

        // After flush, cache is gone so re-reads from DB — should not find deleted key
        $this->assertNull($this->service->get('cached_key'));
    }

    public function test_set_invalidates_cache(): void
    {
        $this->service->set('key', 'original');
        $this->service->all(); // populate cache

        $this->service->set('key', 'updated');

        $this->assertSame('updated', $this->service->get('key'));
    }

    public function test_forget_invalidates_cache(): void
    {
        $this->service->set('key', 'value');
        $this->service->all(); // populate cache

        $this->service->forget('key');

        $this->assertNull($this->service->get('key'));
    }

    public function test_setting_helper_returns_value(): void
    {
        $this->service->set('app_name', 'Boilerplate');

        $this->assertSame('Boilerplate', setting('app_name'));
    }

    public function test_setting_helper_returns_default(): void
    {
        $this->assertSame('default', setting('missing_key', 'default'));
    }
}
