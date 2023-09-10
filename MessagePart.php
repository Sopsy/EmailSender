<?php
declare(strict_types=1);

namespace EmailSender;

final class MessagePart
{
    /**
     * @param string $body
     * @param string $contentType
     * @param array<string, string> $headers
     */
    public function __construct(
        private readonly string $body,
        private readonly string $contentType = 'text/plain',
        private readonly array $headers = []
    ) {
    }

    public function body(): string
    {
        return $this->body;
    }

    public function contentType(): string
    {
        return $this->contentType;
    }

    /** @return array<string, string> */
    public function headers(): array
    {
        return $this->headers;
    }
}