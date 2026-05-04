<?php

namespace Tests\Unit\Shared\Media;

use Illuminate\Database\Eloquent\Model;
use PHPUnit\Framework\TestCase;
use Shared\Media\Contracts\MediaRemover;
use Shared\Media\Services\SpatieMediaRemover;

abstract class RemovableMediaTestModel extends Model
{
    abstract public function clearMediaCollection(string $collection): void;
}

class SpatieMediaRemoverTest extends TestCase
{
    private SpatieMediaRemover $remover;

    protected function setUp(): void
    {
        parent::setUp();

        $this->remover = new SpatieMediaRemover();
    }

    public function test_implements_media_remover_contract(): void
    {
        $this->assertInstanceOf(MediaRemover::class, $this->remover);
    }

    public function test_remove_clears_media_collection(): void
    {
        $model = $this->createMock(RemovableMediaTestModel::class);

        $model->expects($this->once())
            ->method('clearMediaCollection')
            ->with('avatars');

        $this->remover->remove($model, 'avatars');
    }
}
