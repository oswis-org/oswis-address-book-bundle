<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use InvalidArgumentException;
use Zakjakub\OswisCoreBundle\Exceptions\RevisionMissingException;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberContainerTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicContainerTrait;
use function in_array;

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

    /**
     * @param string|null $typeName
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    final public function checkType(?string $typeName): bool
    {
        if (in_array($typeName, self::ALLOWED_TYPES, true)) {
            return true;
        }
        throw new InvalidArgumentException('Typ organizace "'.$typeName.'" není povolen.');
    }
}
