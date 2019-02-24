<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisCoreBundle\Entity\AbstractRevision;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicContainerTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_abstract_contact_revision")
 * @Doctrine\ORM\Mapping\InheritanceType("JOINED")
 * @Doctrine\ORM\Mapping\DiscriminatorColumn(name="discriminator", type="text")
 * @Doctrine\ORM\Mapping\DiscriminatorMap({
 *   "address_book_person_revision" = "PersonRevision",
 *   "address_book_organization_revision" = "OrganizationRevision"
 * })
 */
abstract class AbstractContactRevision extends AbstractRevision
{

    use BasicEntityTrait;

    /**
     * @inheritdoc
     *
     * @return string
     */
    final public function __toString(): string
    {
        return $this->getContactName();
    }

    abstract public function getContactName(): string;

    abstract public function setContactName(?string $dummy): void;

}
