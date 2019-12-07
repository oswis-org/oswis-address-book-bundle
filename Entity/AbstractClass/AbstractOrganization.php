<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Doctrine\Common\Collections\Collection;
use Exception;
use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisAddressBookBundle\Entity\OrganizationRevision;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Traits\Entity\ColorTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\IdentificationNumberTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use function assert;

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
        ?ContactImage $image = null,
        ?Collection $addressBooks = null
    ) {
        parent::__construct($type, $notes, $contactDetails, $addresses, $image, $addressBooks);
        $this->setFieldsFromNameable($nameable);
        $this->setIdentificationNumber($identificationNumber);
        $this->setColor($color);
    }

    final public function destroyRevisions(): void
    {
        try {
            $actualRevision = $this->getRevisionByDate();
            assert($actualRevision instanceof OrganizationRevision);
            $this->setFieldsFromNameable($actualRevision->getNameable());
            $this->setIdentificationNumber($actualRevision->getIdentificationNumber());
            $this->setColor($actualRevision->getColor());
            foreach ($this->getRevisions() as $revision) {
                $this->removeRevision($revision);
            }
            $this->setActiveRevision(null);
        } catch (Exception $e) {
        }
    }

    final public function setFullName(?string $fullName): void
    {
        try {
            $this->setName($fullName);
        } catch (Exception $e) {
            return;
        }
    }

    final public function getFullName(): ?string
    {
        return $this->getName();
    }
}
