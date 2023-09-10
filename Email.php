<?php
declare(strict_types=1);

namespace EmailSender;

use Exception;
use InvalidArgumentException;

use function bin2hex;
use function date;
use function filter_var;
use function mail;
use function mb_encode_mimeheader;
use function random_bytes;

use const FILTER_VALIDATE_EMAIL;

final class Email
{
    /** @var array<string, string> */
    private readonly array $headers;
    private readonly string $body;
    private readonly string $subject;

    /**
     * @param string $toAddress
     * @param string $fromName
     * @param string $fromAddress
     * @param string $replyTo
     * @param array<string, string> $headers
     * @param string $subject
     * @param MessagePart ...$messageParts
     * @throws Exception
     */
    public function __construct(
        private readonly string $toAddress,
        private readonly string $fromName,
        private readonly string $fromAddress,
        string $replyTo,
        array $headers,
        string $subject,
        MessagePart ...$messageParts
    ) {
        if (!$this->validateAddress($toAddress)) {
            throw new InvalidArgumentException("Invalid to address '{$toAddress}'");
        }
        if (!$this->validateAddress($fromAddress)) {
            throw new InvalidArgumentException("Invalid from address '{$fromAddress}'");
        }
        if ($replyTo !== '' && !$this->validateAddress($replyTo)) {
            throw new InvalidArgumentException("Invalid reply-to address '{$replyTo}'");
        }

        $this->subject = mb_encode_mimeheader($subject, 'UTF-8');
        $mimeBoundary = '--------' . bin2hex(random_bytes(16));

        $headers['MIME-Version'] = '1.0';
        $headers['Date'] = date('r (T)');
        $headers['Content-Transfer-Encoding'] = '8bit';
        $headers['Content-Type'] = "multipart/alternative;boundary=\"{$mimeBoundary}\"";
        $headers['From'] = "{$this->fromName} <{$this->fromAddress}>";
        if ($replyTo !== '') {
            $headers['Reply-To'] = $replyTo;
        }

        foreach ($headers as $key => $value) {
            $headers[$key] = $value;
        }
        $this->headers = $headers;

        $body = "This is a multi-part message in MIME format.\r\n";
        foreach ($messageParts as $part) {
            $body .= "--{$mimeBoundary}\r\n";
            $body .= "Content-Type: {$part->contentType()}; charset=UTF-8\r\n";
            $body .= "Content-Transfer-Encoding: 8bit\r\n";
            foreach ($part->headers() as $key => $value) {
                $body .= "{$key}: {$value}\r\n";
            }
            $body .= "\r\n{$part->body()}\r\n\r\n";
        }
        $body .= "--{$mimeBoundary}--";
        $this->body = $body;
    }

    public function send(): bool
    {
        $headers = '';
        foreach ($this->headers as $key => $value) {
            $headers .= "{$key}: {$value}\r\n";
        }

        // Additional sendmail parameters
        $parameters = "-f {$this->fromAddress}";

        // Send mail
        return mail($this->toAddress, $this->subject, $this->body, $headers, $parameters);
    }

    private function validateAddress(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
}