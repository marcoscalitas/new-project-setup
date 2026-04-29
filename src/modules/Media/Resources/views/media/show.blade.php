@extends('admin.layouts.app')

@section('title', __('ui.media'))
@section('page-title', $media->file_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('media.index') }}">{{ __('ui.media') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ $media->file_name }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12 col-md-6">
            <div class="card">
                <div class="card-body">
                    <dl class="row">
                        <dt class="col-sm-4">{{ __('ui.file_name') }}</dt>
                        <dd class="col-sm-8">{{ $media->file_name }}</dd>

                        <dt class="col-sm-4">{{ __('ui.collection') }}</dt>
                        <dd class="col-sm-8">{{ $media->collection_name }}</dd>

                        <dt class="col-sm-4">{{ __('ui.mime_type') }}</dt>
                        <dd class="col-sm-8">{{ $media->mime_type }}</dd>

                        <dt class="col-sm-4">{{ __('ui.size') }}</dt>
                        <dd class="col-sm-8">{{ number_format($media->size / 1024, 1) }} KB</dd>

                        <dt class="col-sm-4">{{ __('ui.created_at') }}</dt>
                        <dd class="col-sm-8">{{ $media->created_at->format('d/m/Y H:i') }}</dd>
                    </dl>

                    <a href="{{ route('media.index') }}" class="btn btn-secondary">{{ __('ui.back') }}</a>

                    @can('delete', \Spatie\MediaLibrary\MediaCollections\Models\Media::class)
                        <form method="POST" action="{{ route('media.destroy', $media->id) }}" style="display:inline">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-danger">{{ __('ui.delete') }}</button>
                        </form>
                    @endcan
                </div>
            </div>
        </div>
    </div>
@endsection
