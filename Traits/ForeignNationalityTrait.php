<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds foreignNationality boolean field.
 */
trait ForeignNationalityTrait
{
    /**
     * Foreign nationality.
     *
     * @var bool|null
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $foreignNationality = null;

    public function getForeignNationality(): bool
    {
        return $this->foreignNationality ?? false;
    }

    /**
     * @param bool $foreignNationality
     */
    public function setForeignNationality(?bool $foreignNationality): void
    {
        $this->foreignNationality = $foreignNationality;
    }
}
