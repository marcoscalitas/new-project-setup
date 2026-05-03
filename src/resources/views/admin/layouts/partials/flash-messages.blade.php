@if (session('success') || session('error') || session('warning') || session('info'))

<svg xmlns="http://www.w3.org/2000/svg" style="display:none">
    <symbol id="pc-flash-check" fill="currentColor" viewBox="0 0 16 16">
        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0zm-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
    </symbol>
    <symbol id="pc-flash-info" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm.93-9.412-1 4.705c-.07.34.029.533.304.533.194 0 .487-.07.686-.246l-.088.416c-.287.346-.92.598-1.465.598-.703 0-1.002-.422-.808-1.319l.738-3.468c.064-.293.006-.399-.287-.47l-.451-.081.082-.381 2.29-.287zM8 5.5a1 1 0 1 1 0-2 1 1 0 0 1 0 2z"/>
    </symbol>
    <symbol id="pc-flash-triangle" fill="currentColor" viewBox="0 0 16 16">
        <path d="M8.982 1.566a1.13 1.13 0 0 0-1.96 0L.165 13.233c-.457.778.091 1.767.98 1.767h13.713c.889 0 1.438-.99.98-1.767L8.982 1.566zM8 5c.535 0 .954.462.9.995l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 5.995A.905.905 0 0 1 8 5zm.002 6a1 1 0 1 1 0 2 1 1 0 0 1 0-2z"/>
    </symbol>
</svg>

@if (session('success'))
    <div class="alert alert-success flex items-center" role="alert" id="flash-success">
        <svg class="flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#pc-flash-check"></use></svg>
        <div>{{ session('success') }}</div>
    </div>
@endif

@if (session('error'))
    <div class="alert alert-danger flex items-center" role="alert" id="flash-error">
        <svg class="flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#pc-flash-triangle"></use></svg>
        <div>{{ session('error') }}</div>
    </div>
@endif

@if (session('warning'))
    <div class="alert alert-warning flex items-center" role="alert" id="flash-warning">
        <svg class="flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#pc-flash-triangle"></use></svg>
        <div>{{ session('warning') }}</div>
    </div>
@endif

@if (session('info'))
    <div class="alert alert-info flex items-center" role="alert" id="flash-info">
        <svg class="flex-shrink-0 me-2" width="24" height="24"><use xlink:href="#pc-flash-info"></use></svg>
        <div>{{ session('info') }}</div>
    </div>
@endif

<script>
    (function () {
        ['flash-success', 'flash-info'].forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            setTimeout(function () {
                el.style.transition = 'opacity 0.5s ease';
                el.style.opacity = '0';
                setTimeout(function () { el.remove(); }, 500);
            }, 5000);
        });
    })();
</script>

@endif
