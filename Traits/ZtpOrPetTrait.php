<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds description field.
 */
trait ZtpOrPetTrait
{
    /**
     * Person is ZTP(P).
     *
     * @var bool|null
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $ztp;

    /**
     * Person is ZTP(P) accompaniment.
     *
     * @var bool|null
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $ztpAccompaniment;

    /**
     * Pet, not person.
     *
     * @var bool|null
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $pet;

    public function isZtp(): bool
    {
        return $this->ztp ?? false;
    }

    public function setZtp(bool $ztp): void
    {
        $this->ztp = $ztp;
    }

    public function isZtpAccompaniment(): bool
    {
        return $this->ztpAccompaniment ?? false;
    }

    public function setZtpAccompaniment(bool $ztpAccompaniment): void
    {
        $this->ztpAccompaniment = $ztpAccompaniment;
    }

    public function isPet(): bool
    {
        return $this->pet ?? false;
    }

    public function setPet(bool $pet): void
    {
        $this->pet = $pet;
    }
}
