<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPersonRevision;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;
use function assert;

/**
 * Class Person
 *
 * Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_person_revision")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class PersonRevision extends AbstractPersonRevision
{

    /**
     * @var AbstractRevisionContainer|null
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     inversedBy="revisions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="container_id", referencedColumnName="id")
     */
    protected ?AbstractRevisionContainer $container;

    /**
     * @param AbstractRevisionContainer|null $revisionContainer
     */
    public static function checkRevisionContainer(?AbstractRevisionContainer $revisionContainer): void
    {
        assert($revisionContainer instanceof Person);
    }

    /**
     * @return string
     */
    public static function getRevisionContainerClassName(): string
    {
        return Person::class;
    }
}
