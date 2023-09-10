<?php
declare(strict_types=1);

namespace EmailSender;

use function explode;
use function implode;
use function str_replace;
use function strip_tags;
use functio trim;

final class BodyConverter
{
    public function __construct(
        private readonly string $body
    ) {
    }

    public function htmlToPlainText(): string
    {
        $content = trim(strip_tags($this->body));
        $content = str_replace("\r", '', $content);
        $content = explode("\n", $content);

        // Trim each text row
        foreach ($content as &$row) {
            $row = trim($row);
        }
        unset($row);

        return implode("\r\n", $content);
    }
}