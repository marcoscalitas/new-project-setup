@extends('admin.layouts.app')

@section('title', __('ui.media'))
@section('page-title', __('ui.media'))

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('home') }}">{{ __('ui.home') }}</a></li>
    <li class="breadcrumb-item" aria-current="page">{{ __('ui.media') }}</li>
@endsection

@section('content')
    <div class="grid grid-cols-12 gap-x-6">
        <div class="col-span-12">
            <div class="card table-card">
                <div class="card-header">
                    <h5>{{ __('ui.media_library') }}</h5>
                </div>
                <div class="card-body pt-3">
                    @if (session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>{{ __('ui.file_name') }}</th>
                                    <th>{{ __('ui.collection') }}</th>
                                    <th>{{ __('ui.mime_type') }}</th>
                                    <th>{{ __('ui.size') }}</th>
                                    <th>{{ __('ui.created_at') }}</th>
                                    <th>{{ __('ui.actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($media as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->file_name }}</td>
                                        <td>{{ $item->collection_name }}</td>
                                        <td>{{ $item->mime_type }}</td>
                                        <td>{{ number_format($item->size / 1024, 1) }} KB</td>
                                        <td>{{ $item->created_at->format('d/m/Y H:i') }}</td>
                                        <td>
                                            @can('delete', \Spatie\MediaLibrary\MediaCollections\Models\Media::class)
                                                <form method="POST" action="{{ route('media.destroy', $item->id) }}" style="display:inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-danger">{{ __('ui.delete') }}</button>
                                                </form>
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">{{ __('ui.no_records') }}</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{ $media->links() }}
                </div>
            </div>
        </div>
    </div>
@endsection
