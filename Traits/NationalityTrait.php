<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use Doctrine\ORM\Mapping\Column;

/**
 * Trait adds description field.
 */
trait NationalityTrait
{
    /**
     * Nationality (as national string).
     */
    #[Column(type: 'string')]
    protected ?string $nationality = null;

    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }
}
