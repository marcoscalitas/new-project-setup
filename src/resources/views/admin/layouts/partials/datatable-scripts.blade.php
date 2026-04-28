@push('scripts')
<script src="{{ asset('admin/theme/plugins/simple-datatables.js') }}"></script>
<script type="module">
    import { DataTable } from '{{ asset("admin/theme/plugins/module.js") }}';
    new DataTable('#pc-dt-simple', {
        columns: [{ select: 0, sortable: false, searchable: false }]
    });
</script>
@endpush
