<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class BackupMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $filePath,
        public string $fileName,
        public int $fileSize,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Respaldo BD — ' . now()->format('d/m/Y H:i'),
        );
    }

    public function content(): Content
    {
        return new Content(
            htmlString: view('emails.backup', [
                'fileName' => $this->fileName,
                'fileSize' => number_format($this->fileSize / 1024, 1) . ' KB',
                'date' => now()->format('d/m/Y H:i:s'),
            ])->render(),
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->filePath)
                ->as($this->fileName)
                ->withMime('application/sql'),
        ];
    }
}
