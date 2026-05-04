<?php

namespace Tests\Unit\Shared\Media;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use PHPUnit\Framework\TestCase;
use Shared\Media\Contracts\MediaUploader;
use Shared\Media\Services\SpatieMediaUploader;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class UploadableMediaTestModel extends Model
{
    abstract public function addMedia(mixed $file): FileAdder;
}

class SpatieMediaUploaderTest extends TestCase
{
    private SpatieMediaUploader $uploader;

    protected function setUp(): void
    {
        parent::setUp();

        $this->uploader = new SpatieMediaUploader();
    }

    public function test_implements_media_uploader_contract(): void
    {
        $this->assertInstanceOf(MediaUploader::class, $this->uploader);
    }

    public function test_upload_adds_media_to_collection(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $media = $this->createMock(Media::class);

        $fileAdder = $this->createMock(FileAdder::class);
        $fileAdder->expects($this->once())
            ->method('toMediaCollection')
            ->with('avatars')
            ->willReturn($media);

        $model = $this->createMock(UploadableMediaTestModel::class);
        $model->expects($this->once())
            ->method('addMedia')
            ->with($file)
            ->willReturn($fileAdder);

        $this->assertSame($media, $this->uploader->upload($model, $file, 'avatars'));
    }

    public function test_upload_can_target_disk(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $media = $this->createMock(Media::class);

        $fileAdder = $this->createMock(FileAdder::class);
        $fileAdder->expects($this->once())
            ->method('toMediaCollection')
            ->with('documents', 'private')
            ->willReturn($media);

        $model = $this->createMock(UploadableMediaTestModel::class);
        $model->method('addMedia')->with($file)->willReturn($fileAdder);

        $this->assertSame($media, $this->uploader->upload($model, $file, 'documents', 'private'));
    }
}
