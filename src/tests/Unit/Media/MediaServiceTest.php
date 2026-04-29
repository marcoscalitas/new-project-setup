<?php

namespace Tests\Unit\Media;

use Illuminate\Database\Eloquent\Model;
use Modules\Core\Contracts\FileUploadInterface;
use Modules\Media\Services\MediaService;
use PHPUnit\Framework\TestCase;
use Spatie\MediaLibrary\MediaCollections\FileAdder;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

abstract class MediaTestModel extends Model
{
    abstract public function addMedia(mixed $file): FileAdder;
    abstract public function clearMediaCollection(string $collection): void;
    abstract public function getFirstMediaUrl(string $collection, string $conversion = ''): string;
}

class MediaServiceTest extends TestCase
{
    private MediaService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MediaService();
    }

    public function test_implements_file_upload_interface(): void
    {
        $this->assertInstanceOf(FileUploadInterface::class, $this->service);
    }

    public function test_upload_adds_media_to_collection_and_returns_url(): void
    {
        $expectedUrl = 'https://cdn.example.com/images/photo.jpg';

        $media = $this->createMock(Media::class);
        $media->method('getUrl')->willReturn($expectedUrl);

        $fileAdder = $this->createMock(FileAdder::class);
        $fileAdder->method('toMediaCollection')->with('images')->willReturn($media);

        $model = $this->createMock(MediaTestModel::class);
        $model->method('addMedia')->with('file.jpg')->willReturn($fileAdder);

        $url = $this->service->upload('file.jpg', 'images', $model);

        $this->assertSame($expectedUrl, $url);
    }

    public function test_delete_clears_media_collection(): void
    {
        $model = $this->createMock(MediaTestModel::class);

        $model->expects($this->once())
            ->method('clearMediaCollection')
            ->with('avatars');

        $this->service->delete($model, 'avatars');
    }

    public function test_get_url_returns_url_when_media_exists(): void
    {
        $expectedUrl = 'https://cdn.example.com/avatars/photo.jpg';

        $model = $this->createMock(MediaTestModel::class);
        $model->method('getFirstMediaUrl')->with('avatars')->willReturn($expectedUrl);

        $url = $this->service->getUrl($model, 'avatars');

        $this->assertSame($expectedUrl, $url);
    }

    public function test_get_url_returns_null_when_no_media(): void
    {
        $model = $this->createMock(MediaTestModel::class);
        $model->method('getFirstMediaUrl')->with('avatars')->willReturn('');

        $url = $this->service->getUrl($model, 'avatars');

        $this->assertNull($url);
    }
}
