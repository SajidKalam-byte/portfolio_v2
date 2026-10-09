<?php

namespace App\Mail;

use App\Models\Testimonial;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TestimonialReceived extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Testimonial $testimonial) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '[Portfolio] New testimonial from '.$this->testimonial->author_name);
    }

    public function content(): Content
    {
        return new Content(text: 'mail.testimonial-received');
    }
}
