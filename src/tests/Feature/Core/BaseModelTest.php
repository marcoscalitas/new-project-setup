<?php

namespace Tests\Feature\Core;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\BaseModel;
use Modules\Core\Traits\HasUlid;
use Tests\TestCase;

class BaseModelTestStub extends BaseModel
{
    protected $table    = 'base_model_stubs';
    protected $fillable = ['name'];
}

class BaseModelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('base_model_stubs', function (Blueprint $table) {
            $table->id();
            $table->char('ulid', 26)->unique();
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('base_model_stubs');
        parent::tearDown();
    }

    public function test_model_has_int_primary_key(): void
    {
        $model = BaseModelTestStub::create(['name' => 'test']);

        $this->assertIsInt($model->id);
        $this->assertGreaterThan(0, $model->id);
    }

    public function test_model_auto_generates_ulid(): void
    {
        $model = BaseModelTestStub::create(['name' => 'test']);

        $this->assertNotNull($model->ulid);
        $this->assertMatchesRegularExpression('/^[0-9A-Z]{26}$/', $model->ulid);
    }

    public function test_primary_key_is_incrementing(): void
    {
        $this->assertTrue((new BaseModelTestStub())->getIncrementing());
    }

    public function test_primary_key_type_is_int(): void
    {
        $this->assertSame('int', (new BaseModelTestStub())->getKeyType());
    }

    public function test_route_key_name_is_ulid(): void
    {
        $this->assertSame('ulid', (new BaseModelTestStub())->getRouteKeyName());
    }

    public function test_model_uses_soft_deletes(): void
    {
        $this->assertContains(SoftDeletes::class, class_uses_recursive(BaseModelTestStub::class));
    }

    public function test_soft_delete_sets_deleted_at_instead_of_removing_row(): void
    {
        $model = BaseModelTestStub::create(['name' => 'to delete']);

        $model->delete();

        $this->assertSoftDeleted('base_model_stubs', ['id' => $model->id]);
    }

    public function test_soft_deleted_model_is_excluded_from_queries(): void
    {
        $model = BaseModelTestStub::create(['name' => 'gone']);
        $model->delete();

        $this->assertNull(BaseModelTestStub::find($model->id));
    }

    public function test_soft_deleted_model_is_found_with_trashed(): void
    {
        $model = BaseModelTestStub::create(['name' => 'trashed']);
        $model->delete();

        $this->assertNotNull(BaseModelTestStub::withTrashed()->find($model->id));
    }

    public function test_model_can_be_restored(): void
    {
        $model = BaseModelTestStub::create(['name' => 'restore me']);
        $model->delete();
        $model->restore();

        $this->assertNotNull(BaseModelTestStub::find($model->id));
    }

    public function test_base_model_is_abstract(): void
    {
        $reflection = new \ReflectionClass(BaseModel::class);

        $this->assertTrue($reflection->isAbstract());
    }
}
