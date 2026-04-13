{{-- One-time reset so the new default (sidebar closed) applies for anyone who had Filament persist keys. --}}
<script>
    (function () {
        var k = 'fi.sidebar.defaultClosedMigration.v1';
        if (localStorage.getItem(k)) {
            return;
        }
        localStorage.setItem(k, '1');
        try {
            localStorage.removeItem('isOpen');
            localStorage.removeItem('isOpenDesktop');
        } catch (e) {}
    })();
</script>
