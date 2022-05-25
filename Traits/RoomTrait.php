<?php
/** @noinspection PhpUnused */

/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use Doctrine\ORM\Mapping\Column;
use OswisOrg\OswisCoreBundle\Traits\Common\DateTimeTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;

/**
 * Trait adds room fields.
 */
trait RoomTrait
{
    use NameableTrait;
    use DateTimeTrait;

    /** Floor number. */
    #[Column(type: 'smallint', nullable: true)]
    protected ?int $floor = null;

    /** Number of regular beds. */
    #[Column(type: 'smallint', nullable: true)]
    protected ?int $numberOfBeds = null;

    /** Number of extra beds. */
    #[Column(type: 'smallint', nullable: true)]
    protected ?int $numberOfExtraBeds = null;

    /** Number of animals. */
    #[Column(type: 'smallint', nullable: true)]
    protected ?int $numberOfAnimals = null;

    public function getFloor(): ?int
    {
        return $this->floor;
    }

    public function setFloor(?int $floor): void
    {
        $this->floor = $floor;
    }

    public function getNumberOfBeds(): int
    {
        return $this->numberOfBeds ?? 0;
    }

    public function setNumberOfBeds(?int $numberOfBeds): void
    {
        $this->numberOfBeds = $numberOfBeds;
    }

    public function getNumberOfExtraBeds(): int
    {
        return $this->numberOfExtraBeds ?? 0;
    }

    public function setNumberOfExtraBeds(?int $numberOfExtraBeds): void
    {
        $this->numberOfExtraBeds = $numberOfExtraBeds;
    }

    public function getNumberOfAnimals(): int
    {
        return $this->numberOfAnimals ?? 0;
    }

    public function setNumberOfAnimals(?int $numberOfAnimals): void
    {
        $this->numberOfAnimals = $numberOfAnimals;
    }
}
