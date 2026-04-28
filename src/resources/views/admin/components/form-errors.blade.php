@if ($errors->any())
    <div class="mb-4 p-3 rounded bg-danger-500/10 border border-danger-500/20 text-sm text-danger-500">
        @foreach ($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif
