<?php

declare(strict_types=1);

namespace App\Model\Enquiry;

use App\EventSourcing\Aggregate\AggregateRoot;
use App\EventSourcing\AppliesAggregateChanged;
use App\Model\Email;
use App\Model\Enquiry\Event\EnquiryWasSubmitted;
use App\Model\Entity;

class Enquiry extends AggregateRoot implements Entity
{
    use AppliesAggregateChanged;

    public const NAME_MIN_LENGTH = 5;
    public const NAME_MAX_LENGTH = 50;
    public const MESSAGE_MIN_LENGTH = 10;
    public const MESSAGE_MAX_LENGTH = 5000;

    /** @var EnquiryId */
    private $enquiryId;

    public static function submit(
        EnquiryId $enquiryId,
        string $name,
        Email $email,
        string $message
    ): self {
        $self = new self();
        $self->recordThat(
            EnquiryWasSubmitted::now($enquiryId, $name, $email, $message)
        );

        return $self;
    }

    protected function aggregateId(): string
    {
        return $this->enquiryId->toString();
    }

    protected function whenEnquiryWasSubmitted(EnquiryWasSubmitted $event): void
    {
        $this->enquiryId = $event->enquiryId();
    }

    /**
     * @param Enquiry|Entity $other
     */
    public function sameIdentityAs(Entity $other): bool
    {
        return get_class($this) === get_class($other) && $this->enquiryId->sameValueAs($other->enquiryId);
    }
}
