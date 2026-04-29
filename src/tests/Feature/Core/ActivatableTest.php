<?php

namespace Tests\Feature\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Traits\Activatable;
use Tests\TestCase;

class ActivatableTestModel extends Model
{
    use Activatable;

    protected $table    = 'activatable_test_models';
    protected $fillable = ['name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
    public $timestamps  = false;
}

class ActivatableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('activatable_test_models', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->boolean('is_active')->default(true);
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('activatable_test_models');
        parent::tearDown();
    }

    public function test_activate_sets_is_active_to_true(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => false]);

        $model->activate();

        $this->assertTrue($model->fresh()->is_active);
    }

    public function test_deactivate_sets_is_active_to_false(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => true]);

        $model->deactivate();

        $this->assertFalse($model->fresh()->is_active);
    }

    public function test_is_active_returns_true_when_active(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => true]);

        $this->assertTrue($model->isActive());
    }

    public function test_is_active_returns_false_when_inactive(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => false]);

        $this->assertFalse($model->isActive());
    }

    public function test_scope_active_returns_only_active_records(): void
    {
        ActivatableTestModel::create(['name' => 'active',   'is_active' => true]);
        ActivatableTestModel::create(['name' => 'inactive', 'is_active' => false]);

        $results = ActivatableTestModel::active()->get();

        $this->assertCount(1, $results);
        $this->assertSame('active', $results->first()->name);
    }

    public function test_scope_inactive_returns_only_inactive_records(): void
    {
        ActivatableTestModel::create(['name' => 'active',   'is_active' => true]);
        ActivatableTestModel::create(['name' => 'inactive', 'is_active' => false]);

        $results = ActivatableTestModel::inactive()->get();

        $this->assertCount(1, $results);
        $this->assertSame('inactive', $results->first()->name);
    }

    public function test_activate_persists_to_database(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => false]);

        $model->activate();

        $this->assertTrue(
            ActivatableTestModel::where('id', $model->id)->value('is_active'),
        );
    }

    public function test_deactivate_persists_to_database(): void
    {
        $model = ActivatableTestModel::create(['name' => 'item', 'is_active' => true]);

        $model->deactivate();

        $this->assertFalse(
            (bool) ActivatableTestModel::where('id', $model->id)->value('is_active'),
        );
    }
}
