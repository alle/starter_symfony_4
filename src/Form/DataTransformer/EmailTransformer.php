<?php

declare(strict_types=1);

namespace App\Form\DataTransformer;

use App\Model\Email;
use Symfony\Component\Form\DataTransformerInterface;

class EmailTransformer implements DataTransformerInterface
{
    /**
     * @param Email|null|string $email
     */
    public function transform($email): ?string
    {
        if (null === $email) {
            return null;
        }

        return $email->toString();
    }

    public function reverseTransform($email): ?Email
    {
        if (null === $email) {
            return null;
        }

        return Email::fromString($email);
    }
}
