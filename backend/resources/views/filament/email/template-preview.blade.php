@php
    use App\Services\Email\EmailTemplateRenderer;
    use App\Services\Email\EmailTestSampleData;

    $record = $getRecord();
    $preview = null;
    if ($record) {
        $rendered = app(EmailTemplateRenderer::class)->render($record, EmailTestSampleData::forTemplate($record));
        $preview = [
            'subject' => $rendered['subject'] ?? '',
            'html' => view('mail.layouts.brand', $rendered)->render(),
        ];
    }
@endphp
@if ($preview)
    <section class="tb-mail-preview">
        <header>
            <strong>Preview</strong>
            <span>Subject: {{ $preview['subject'] }}</span>
        </header>
        <iframe title="Email preview" sandbox srcdoc="{{ $preview['html'] }}"></iframe>
    </section>
@endif
