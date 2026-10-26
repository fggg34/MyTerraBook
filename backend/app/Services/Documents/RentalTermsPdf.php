<?php

namespace App\Services\Documents;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Mail\Mailables\Attachment;
use Symfony\Component\HttpFoundation\Response;

class RentalTermsPdf
{
    public function stream(string $reference, ?string $customer, ?string $terms): Response
    {
        if (! filled($terms)) {
            abort(404, 'This booking has no rental terms.');
        }

        return Pdf::loadView('pdf.rental-terms-document', [
            'reference' => $reference,
            'customer' => $customer,
            'terms' => $terms,
        ])->stream('rental-terms-'.$reference.'.pdf');
    }

    public function attachment(string $reference, ?string $customer, ?string $terms): ?Attachment
    {
        if (! filled($terms)) {
            return null;
        }

        return Attachment::fromData(
            fn (): string => Pdf::loadView('pdf.rental-terms-document', [
                'reference' => $reference,
                'customer' => $customer,
                'terms' => $terms,
            ])->output(),
            'rental-terms-'.$reference.'.pdf',
        )->withMime('application/pdf');
    }
}
