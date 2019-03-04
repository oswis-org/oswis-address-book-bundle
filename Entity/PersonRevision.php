<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPersonRevision;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;

/**
 * Class Person
 *
 * Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_person_revision")
 * @ApiResource(
 *   iri="http://schema.org/Person"
 * )
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "ipartial", "familyName": "ipartial"})
 */
class PersonRevision extends AbstractPersonRevision
{

    /**
     * @var Person
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Person",
     *     inversedBy="revisions"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="container_id", referencedColumnName="id")
     */
    protected $container;

    /**
     * @param AbstractRevisionContainer|null $revisionContainer
     */
    public static function checkRevisionContainer(?AbstractRevisionContainer $revisionContainer): void
    {
        \assert($revisionContainer instanceof Person);
    }

    /**
     * @return string
     */
    public static function getRevisionContainerClassName(): string
    {
        return Person::class;
    }
}
