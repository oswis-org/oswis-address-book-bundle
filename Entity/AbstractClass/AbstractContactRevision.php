<?php

namespace Zakjakub\OswisAddressBookBundle\Entity\AbstractClass;

use ApiPlatform\Core\Annotation\ApiProperty;
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
 *   "person" = "PersonRevision",
 *   "organization" = "OrganizationRevision"
 * })
 */
abstract class AbstractContactRevision extends AbstractRevision
{

    use BasicEntityTrait;
    use NameableBasicContainerTrait;

    /**
     * @var ContactImage|null
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactImage"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(nullable=true)
     * @ApiProperty(iri="http://schema.org/image")
     */
    public $image;

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
