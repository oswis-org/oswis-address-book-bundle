<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractOrganizationRevision;
use Zakjakub\OswisCoreBundle\Entity\AbstractClass\AbstractRevisionContainer;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_organization_revision")
 * @ApiResource(
 *   iri="http://schema.org/Organization"
 * )
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "ipartial"})
 */
class OrganizationRevision extends AbstractOrganizationRevision
{

    /**
     * @var PersonAgeRange
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\Organization",
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
        \assert($revisionContainer instanceof Organization);
    }

    /**
     * @return string
     */
    public static function getRevisionContainerClassName(): string
    {
        return Organization::class;
    }

}
