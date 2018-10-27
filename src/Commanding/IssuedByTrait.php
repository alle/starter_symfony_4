<?php

declare(strict_types=1);

namespace App\Commanding;

trait IssuedByTrait
{
    public function withIssuedBy(string $issuer): DomainMessage
    {
        $messageData = $this->toArray();

        $messageData['metadata']['issuedBy'] = $issuer;

        return static::fromArray($messageData);
    }
}
