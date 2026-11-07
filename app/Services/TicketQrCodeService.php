<?php

namespace App\Services;

use App\Models\Ticket;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Support\Collection;

class TicketQrCodeService
{
    public function dataUri(string $ticketToken): string
    {
        $qrCode = new QrCode(
            data: $ticketToken,
            errorCorrectionLevel: ErrorCorrectionLevel::High,
            size: 240,
            margin: 10,
            roundBlockSizeMode: RoundBlockSizeMode::Margin,
            foregroundColor: new Color(15, 23, 42),
            backgroundColor: new Color(255, 255, 255),
        );

        return (new SvgWriter)->write($qrCode)->getDataUri();
    }

    /**
     * @param  Collection<int, Ticket>  $tickets
     * @return array<int, string>
     */
    public function forTickets(Collection $tickets): array
    {
        return $tickets
            ->mapWithKeys(fn ($ticket) => [$ticket->id => $this->dataUri($ticket->qr_token)])
            ->all();
    }
}
