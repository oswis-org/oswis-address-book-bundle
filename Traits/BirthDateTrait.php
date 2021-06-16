<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Traits;

use DateTime;
use Exception;
use OswisOrg\OswisAddressBookBundle\Utils\AgeUtils;

/**
 * Trait adds birthDate field.
 */
trait BirthDateTrait
{
    /**
     * @Doctrine\ORM\Mapping\Column(type="datetime", nullable=true, options={"default" : null})
     */
    protected ?DateTime $birthDate = null;

    /**
     * @param  DateTime|null  $referenceDateTime
     *
     * @return int|null
     * @throws Exception
     */
    public function getAge(?DateTime $referenceDateTime = null): ?int
    {
        return AgeUtils::getAgeFromBirthDate($this->birthDate, $referenceDateTime);
    }

    /**
     * @param  DateTime|null  $referenceDateTime
     *
     * @return int|null
     * @throws Exception
     */
    public function getAgeDecimal(?DateTime $referenceDateTime = null): ?int
    {
        return AgeUtils::getAgeDecimalFromBirthDate($this->birthDate, $referenceDateTime);
    }

    /**
     * Get birth date.
     */
    public function getBirthDate(): ?DateTime
    {
        if ($this->birthDate instanceof DateTime) {
            $this->birthDate->setTime(0, 0);
        }

        return $this->birthDate;
    }

    /**
     * Set date and time of entity update.
     *
     * @param  DateTime|null  $birthDate
     */
    public function setBirthDate(?DateTime $birthDate): void
    {
        if ($birthDate instanceof DateTime) {
            $birthDate->setTime(0, 0);
        }
        $this->birthDate = $birthDate;
    }
}
