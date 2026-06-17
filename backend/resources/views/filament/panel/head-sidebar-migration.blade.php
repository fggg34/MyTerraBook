{{-- One-time reset so the new default (sidebar collapsed, icons only) applies for existing sessions. --}}
<script>
    (function () {
        var k = 'fi.sidebar.defaultClosedMigration.v2';
        if (localStorage.getItem(k)) {
            return;
        }
        localStorage.setItem(k, '1');
        try {
            localStorage.removeItem('isOpen');
            localStorage.removeItem('isOpenDesktop');
            localStorage.removeItem('fi.sidebar.pinned');
        } catch (e) {}
    })();
</script>
