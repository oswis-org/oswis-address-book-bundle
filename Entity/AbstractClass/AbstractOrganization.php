<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicContainerTrait;

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
        self::HIGH_SCHOOL,
        self::STUDENT_ORGANIZATION,
    ];

    use NameableBasicContainerTrait;
    use IdentificationNumberContainerTrait;
    use ColorContainerTrait;

    final public function getContactName(): string
    {
        return $this->getName();
    }

    /**
     * @param string|null $name
     *
     * @throws RevisionMissingException
     */
    final public function setContactName(?string $name): void
    {
        $this->setName($name);
    }
}
