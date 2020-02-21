<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Doctrine\Common\Collections\Collection;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Entity\Publicity;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;

abstract class AbstractOrganization extends AbstractContact
{
    public const ORGANIZATION = 'organization';
    public const COMPANY = 'company';
    public const DEPARTMENT = 'department';
    public const UNIVERSITY = 'university';
    public const FACULTY = 'faculty';
    public const SCHOOL = 'school';
    public const HIGH_SCHOOL = 'high-school';
    public const STUDENT_ORGANIZATION = 'student-organization';

    public const ALLOWED_TYPES = [
        self::ORGANIZATION,
        self::COMPANY,
        self::DEPARTMENT,
        self::UNIVERSITY,
        self::FACULTY,
        self::SCHOOL,
        self::HIGH_SCHOOL,
        self::STUDENT_ORGANIZATION,
    ];

    use NameableBasicTrait;
    use IdentificationNumberTrait;
    use ColorTrait;

    public function __construct(
        ?Nameable $nameable = null,
        ?string $identificationNumber = null,
        ?string $color = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $addressBooks = null,
        ?Publicity $publicity = null
    ) {
        parent::__construct($type, $notes, $contactDetails, $addresses, $addressBooks, null, $publicity);
        $this->setFieldsFromNameable($nameable);
        $this->setIdentificationNumber($identificationNumber);
        $this->setColor($color);
    }

    public function destroyRevisions(): void
    {
    }

    public function setFullName(?string $fullName): void
    {
        $this->setName($fullName);
    }

    public function getFullName(): ?string
    {
        return $this->getName();
    }
}
