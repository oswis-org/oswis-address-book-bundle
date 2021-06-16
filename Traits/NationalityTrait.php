<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds description field.
 */
trait NationalityTrait
{
    /**
     * Nationality (as national string).
     *
     * @Doctrine\ORM\Mapping\Column(type="string")
     */
    protected ?string $nationality = null;

    /**
     * @return string
     */
    public function getNationality(): ?string
    {
        return $this->nationality;
    }

    /**
     * @param  string|null  $nationality
     */
    public function setNationality(?string $nationality): void
    {
        $this->nationality = $nationality;
    }
}
