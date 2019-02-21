<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\SearchFilter;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractPersonRevision;
use Zakjakub\OswisCoreBundle\Entity\AbstractRevisionContainer;

/**
 * Class Person
 *
 * Represents a person.
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="person")
 * @ApiResource(
 *   iri="http://schema.org/Person",
 *   attributes={
 *     "force_eager"=false,
 *     "access_control"="is_granted('ROLE_ADMIN')",
 *     "normalization_context"={"groups"={"persons_get"}},
 *     "denormalization_context"={"groups"={"persons_post"}}
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "normalization_context"={"groups"={"persons_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"persons_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_ADMIN') or object.canRead(user)",
 *       "normalization_context"={"groups"={"person_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_ADMIN') or object.canEdit(user)",
 *       "denormalization_context"={"groups"={"person_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN') or object.canEdit(user)",
 *       "denormalization_context"={"groups"={"person_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @ApiFilter(SearchFilter::class, properties={"id": "exact", "name": "ipartial", "familyName": "ipartial"})
 */
class PersonRevision extends AbstractPersonRevision
{

    /**
     * @var PersonAgeRange
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="Zakjakub\OswisAccommodationBundle\Entity\Person", inversedBy="revisions")
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
