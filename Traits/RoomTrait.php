<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds room fields.
 */
trait RoomTrait
{
    use NameableTrait;
    use DateTimeTrait;

    /**
     * Floor number.
     *
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
    protected ?int $floor = null;

    /**
     * Number of regular beds.
     *
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
    protected ?int $numberOfBeds = null;

    /**
     * Number of extra beds.
     *
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
    protected ?int $numberOfExtraBeds = null;

    /**
     * Number of animals.
     *
     * @var int|null
     * @Doctrine\ORM\Mapping\Column(type="smallint", nullable=true)
     */
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

    /**
     * @param int|null $numberOfBeds
     */
    public function setNumberOfBeds(?int $numberOfBeds): void
    {
        $this->numberOfBeds = $numberOfBeds;
    }

    public function getNumberOfExtraBeds(): int
    {
        return $this->numberOfExtraBeds ?? 0;
    }

    /**
     * @param int|null $numberOfExtraBeds
     */
    public function setNumberOfExtraBeds(?int $numberOfExtraBeds): void
    {
        $this->numberOfExtraBeds = $numberOfExtraBeds;
    }

    public function getNumberOfAnimals(): int
    {
        return $this->numberOfAnimals ?? 0;
    }

    /**
     * @param int|null $numberOfAnimals
     */
    public function setNumberOfAnimals(?int $numberOfAnimals): void
    {
        $this->numberOfAnimals = $numberOfAnimals;
    }
}
