<?php

namespace Tests\Feature\Core;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Modules\Core\Traits\HasUuid;
use Tests\TestCase;

class HasUuidTestModel extends Model
{
    use HasUuid;

    protected $table    = 'has_uuid_test_models';
    protected $fillable = ['name'];
    public $timestamps  = false;
}

class HasUuidTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('has_uuid_test_models', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('name');
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('has_uuid_test_models');
        parent::tearDown();
    }

    public function test_uuid_is_set_on_create(): void
    {
        $model = HasUuidTestModel::create(['name' => 'test']);

        $this->assertNotNull($model->id);
        $this->assertNotEmpty($model->id);
    }

    public function test_uuid_is_valid_format(): void
    {
        $model = HasUuidTestModel::create(['name' => 'test']);

        $this->assertMatchesRegularExpression(
            '/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/',
            $model->id,
        );
    }

    public function test_each_model_gets_unique_uuid(): void
    {
        $a = HasUuidTestModel::create(['name' => 'first']);
        $b = HasUuidTestModel::create(['name' => 'second']);

        $this->assertNotSame($a->id, $b->id);
    }

    public function test_existing_uuid_is_not_overwritten(): void
    {
        $existingId = 'aaaaaaaa-bbbb-cccc-dddd-eeeeeeeeeeee';

        $model       = new HasUuidTestModel(['name' => 'test']);
        $model->id   = $existingId;
        $model->save();

        $this->assertSame($existingId, $model->id);
    }

    public function test_get_incrementing_returns_false(): void
    {
        $model = new HasUuidTestModel();

        $this->assertFalse($model->getIncrementing());
    }

    public function test_get_key_type_returns_string(): void
    {
        $model = new HasUuidTestModel();

        $this->assertSame('string', $model->getKeyType());
    }

    public function test_model_can_be_found_by_uuid(): void
    {
        $created = HasUuidTestModel::create(['name' => 'find me']);

        $found = HasUuidTestModel::find($created->id);

        $this->assertNotNull($found);
        $this->assertSame($created->id, $found->id);
    }
}
