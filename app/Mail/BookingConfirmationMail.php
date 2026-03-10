<?php

namespace App\Mail;

use App\Models\Booking;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BookingConfirmationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 30, 60];

    public Booking $booking;

    public function __construct(Booking $booking)
    {
        $this->booking = $booking->loadMissing(['package', 'rooms.roomType', 'addOns.packageAddOn']);
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Booking Confirmation - ' . $this->booking->uuid,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.booking-confirmation',
            with: [
                'booking' => $this->booking,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
