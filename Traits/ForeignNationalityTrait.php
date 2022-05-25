<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Trait adds foreignNationality boolean field.
 */
trait ForeignNationalityTrait
{
    /** Foreign nationality. */
    #[Column(type: 'boolean', nullable: true)]
    protected ?bool $foreignNationality = null;

    public function getForeignNationality(): bool
    {
        return $this->foreignNationality ?? false;
    }

    /**
     * @param  bool  $foreignNationality
     */
    public function setForeignNationality(?bool $foreignNationality): void
    {
        $this->foreignNationality = $foreignNationality;
    }
}
