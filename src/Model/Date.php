<?php

declare(strict_types=1);

namespace App\Model;

use Carbon\Carbon;

// @todo tests
// @todo switch to CarbonImmutable?
class Date implements ValueObject, \JsonSerializable
{
    public const STRING_FORMAT = 'Y-m-d';
    public const TZ = 'America/Edmonton';

    /** @var Carbon */
    private $date;

    public static function fromString(string $date): self
    {
        return new static(new Carbon($date, self::TZ));
    }

    public static function now(string $tz = null): self
    {
        return new static(new Carbon('now', $tz));
    }

    public static function fromDateTime(\DateTime $date): self
    {
        return new static(Carbon::instance($date));
    }

    public static function fromImmutable(\DateTimeImmutable $date): self
    {
        return new static(Carbon::instance($date));
    }

    private function __construct(Carbon $date)
    {
        $this->date = $date;
    }

    public function date(): Carbon
    {
        return $this->date;
    }

    public function format(string $format): string
    {
        return $this->date->format($format);
    }

    public function toString(): string
    {
        return $this->format(self::STRING_FORMAT);
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    public function jsonSerialize(): string
    {
        return $this->date->jsonSerialize();
    }

    /**
     * @param Date|ValueObject $other
     */
    public function sameValueAs(ValueObject $other): bool
    {
        return get_class($this) === get_class($other) && $this->date->equalTo($other->date);
    }
}
