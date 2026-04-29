<?php

namespace Tests\Feature\Core;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Models\BaseModel;
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
            $table->uuid('id')->primary();
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

    public function test_model_uses_uuid_primary_key(): void
    {
        $model = BaseModelTestStub::create(['name' => 'test']);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $model->id,
        );
    }

    public function test_primary_key_is_not_incrementing(): void
    {
        $this->assertFalse((new BaseModelTestStub())->getIncrementing());
    }

    public function test_primary_key_type_is_string(): void
    {
        $this->assertSame('string', (new BaseModelTestStub())->getKeyType());
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
