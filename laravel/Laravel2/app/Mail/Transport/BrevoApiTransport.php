<?php

namespace App\Mail\Transport;

use Illuminate\Support\Facades\Http;
use RuntimeException;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;

class BrevoApiTransport extends AbstractTransport
{
    public function __construct(
        private readonly string $apiKey,
        private readonly ?string $defaultFromAddress,
        private readonly ?string $defaultFromName,
    ) {
        parent::__construct();
    }

    protected function doSend(SentMessage $message): void
    {
        if ($this->apiKey === '') {
            throw new RuntimeException('The Brevo API key is not configured.');
        }

        $email = $message->getOriginalMessage();

        if (! $email instanceof Email) {
            throw new RuntimeException('Brevo can only send email messages.');
        }

        $from = $email->getFrom()[0] ?? null;
        $from ??= $this->defaultFromAddress
            ? new Address($this->defaultFromAddress, $this->defaultFromName ?? '')
            : null;

        if (! $from) {
            throw new RuntimeException('A verified MAIL_FROM_ADDRESS is required to send email through Brevo.');
        }

        $htmlBody = $email->getHtmlBody();
        $textBody = $email->getTextBody();

        $payload = [
            'sender' => $this->formatAddress($from),
            'subject' => $email->getSubject() ?? '',
            ...($htmlBody !== null
                ? ['htmlContent' => $htmlBody]
                : ['textContent' => $textBody ?? '']),
        ];

        foreach ([
            'to' => $email->getTo(),
            'cc' => $email->getCc(),
            'bcc' => $email->getBcc(),
        ] as $field => $addresses) {
            if ($addresses !== []) {
                $payload[$field] = array_map($this->formatAddress(...), $addresses);
            }
        }

        Http::acceptJson()
            ->withHeaders(['api-key' => $this->apiKey])
            ->timeout(15)
            ->post('https://api.brevo.com/v3/smtp/email', $payload)
            ->throw();
    }

    private function formatAddress(Address $address): array
    {
        $formatted = ['email' => $address->getAddress()];

        if ($address->getName() !== '') {
            $formatted['name'] = $address->getName();
        }

        return $formatted;
    }

    public function __toString(): string
    {
        return 'brevo';
    }
}
