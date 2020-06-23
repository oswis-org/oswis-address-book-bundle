<?php
/**
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Utils;

use DateTime;
use Exception;
use function floor;
use const PHP_INT_MAX;

/**
 * Class AgeUtils.
 *
 * @author  Jakub Zak <mail@jakubzak.eu>
 */
class AgeUtils
{
    /**
     * True if person belongs to age range (at some moment - referenceDateTime).
     *
     * @param DateTime|null $birthDate         BirthDate for age calculation
     * @param int           $minAge            Minimal age, included (default is 0)
     * @param int           $maxAge            maximal age, included (default is infinity)
     * @param DateTime|null $referenceDateTime Reference date, default is _now_
     *
     * @return bool True if birth date belongs to age range interval.
     *
     * @throws Exception
     */
    public static function isBirthDateInRange(
        ?DateTime $birthDate,
        int $minAge = null,
        int $maxAge = null,
        DateTime $referenceDateTime = null
    ): bool {
        if (null === $birthDate) {
            return false;
        }
        $referenceDateTime ??= new DateTime();
        $age = self::getAgeFromBirthDate($birthDate, $referenceDateTime);

        return $age >= ($minAge ?? 0) && $age <= ($maxAge ?? PHP_INT_MAX);
    }

    /**
     * @param DateTime|null $birthDate
     * @param DateTime|null $referenceDateTime
     *
     * @return int|null
     * @throws Exception
     */
    public static function getAgeFromBirthDate(?DateTime $birthDate, DateTime $referenceDateTime = null): ?int
    {
        return $birthDate ? (int)floor(self::getAgeDecimalFromBirthDate($birthDate, $referenceDateTime)) : null;
    }

    /**
     * @param DateTime|null $birthDate
     * @param DateTime|null $referenceDateTime
     *
     * @return int
     */
    public static function getAgeDecimalFromBirthDate(?DateTime $birthDate, ?DateTime $referenceDateTime = null): ?int
    {
        if (!($birthDate instanceof DateTime)) {
            return null;
        }
        $referenceDateTime ??= new DateTime();
        assert($referenceDateTime instanceof DateTime);
        $referenceDateTime->setTime(0, 0);
        $birthDate->setTime(0, 0);

        /// TODO: Return decimal!
        return $birthDate->diff($referenceDateTime)->y;
    }
}
