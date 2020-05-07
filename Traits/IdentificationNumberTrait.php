<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds company identification number.
 */
trait IdentificationNumberTrait
{
    /**
     * Identification number.
     *
     * @var string|null
     *
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     * @Symfony\Component\Validator\Constraints\Length(
     *      min = 6,
     *      max = 10,
     *      minMessage = "IČ {{ value }} je příliš krátké, musí obsahovat nejméně {{ limit }} znaků.",
     *      maxMessage = "IČ {{ value }} je příliš dlouhé, musí obsahovat nejvíce {{ limit }} znaků.",
     * )
     */
    protected ?string $identificationNumber = null;

    /**
     * @return string
     */
    public function getIdentificationNumber(): ?string
    {
        return $this->identificationNumber;
    }

    public function setIdentificationNumber(?string $identificationNumber): void
    {
        $this->identificationNumber = $identificationNumber;
    }
}
