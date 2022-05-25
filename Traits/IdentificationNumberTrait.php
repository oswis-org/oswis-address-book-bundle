<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use Doctrine\ORM\Mapping\Column;
use Symfony\Component\Validator\Constraints\Length;

/**
 * Trait adds company identification number.
 */
trait IdentificationNumberTrait
{
    #[Column(type: 'string', nullable: true)]
    #[Length(min: 6, max: 10, minMessage: 'IČ {{ value }} je příliš krátké, musí obsahovat nejméně {{ limit }} znaků.', maxMessage: 'IČ {{ value }} je příliš dlouhé, musí obsahovat nejvíce {{ limit }} znaků.',)]
    protected ?string $identificationNumber = null;

    /** @noinspection PhpUnusedParameterInspection */
    public function getIdentificationNumber(bool $recursive = false): ?string
    {
        return $this->identificationNumber;
    }

    public function setIdentificationNumber(?string $identificationNumber): void
    {
        $this->identificationNumber = $identificationNumber;
    }
}
