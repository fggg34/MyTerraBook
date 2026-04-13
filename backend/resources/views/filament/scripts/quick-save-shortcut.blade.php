{{-- Prevent the browser “Save page” dialog on ⌘S / Ctrl+S when a Filament schema form is present. Filament already binds mod+s to the Save action. --}}
<script>
    document.addEventListener(
        'keydown',
        function (e) {
            if (!(e.ctrlKey || e.metaKey) || String(e.key).toLowerCase() !== 's') {
                return;
            }
            if (e.shiftKey) {
                return;
            }
            if (!document.querySelector('.fi-page .fi-sc-form')) {
                return;
            }
            e.preventDefault();
        },
        true,
    );
</script>
