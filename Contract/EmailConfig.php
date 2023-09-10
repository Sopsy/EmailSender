<?php
declare(strict_types=1);

namespace EmailSender\Contract;

interface EmailConfig
{
    public function hashType(): string;

    public function hashOptions(): array;

    public function fromName(): string;

    public function fromAddress(): string;

    public function replyToAddress(): string;
}