<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

/**
 * Trait adds description field.
 */
trait IdCardTrait
{
    /**
     * ID card type (as string).
     *
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected ?string $idCardType = null;

    /**
     * ID card number (as string).
     *
     * @var string|null
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected ?string $idCardNumber = null;

    /**
     * @return string
     */
    public function getIdCardType(): ?string
    {
        return $this->idCardType;
    }

    /**
     * @param  string|null  $idCardType
     */
    public function setIdCardType(?string $idCardType): void
    {
        $this->idCardType = $idCardType;
    }

    /**
     * @return string
     */
    public function getIdCardNumber(): ?string
    {
        return $this->idCardNumber;
    }

    /**
     * @param  string|null  $idCardNumber
     */
    public function setIdCardNumber(?string $idCardNumber): void
    {
        $this->idCardNumber = $idCardNumber;
    }
}
