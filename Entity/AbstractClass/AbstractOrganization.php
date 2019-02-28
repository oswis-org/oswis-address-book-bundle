<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisCoreBundle\Traits\Entity\ColorContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\EmailContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\UrlContainerTrait;

abstract class AbstractOrganization extends AbstractContact
{

    public const ALLOWED_TYPES = [
        'organization',
        'company',
        'department',
        'university',
        'faculty',
        'high-school',
        'student-organization',
    ];

    use NameableBasicContainerTrait;
    use IdentificationNumberContainerTrait;
    use UrlContainerTrait;
    use EmailContainerTrait;
    use ColorContainerTrait;

    final public function getContactName(): string
    {
        return $this->getName();
    }

    /**
     * @param string|null $dummy
     *
     * @throws \Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException
     */
    final public function setContactName(?string $dummy): void
    {
        $this->setName($dummy);
    }

    final public function checkType(?string $typeName): bool
    {
        if (\in_array($typeName, self::ALLOWED_TYPES, true)) {
            return true;
        }
        throw new \InvalidArgumentException('Typ organizace "'.$typeName.'" není povolen.');
    }

}
