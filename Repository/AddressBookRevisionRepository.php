<?php

namespace Zakjakub\OswisAddressBookBundle\Repository;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBookRevision;

class AddressBookRevisionRepository extends EntityRepository
{

    /**
     * @param string        $slug
     * @param DateTime|null $referenceDateTime
     *
     * @return Event|null
     * @throws NonUniqueResultException
     */
    final public function findOneActiveBySlug(string $slug, ?DateTime $referenceDateTime = null): ?Event
    {
        $eventRevisions = new ArrayCollection(
            $this->createQueryBuilder('address_book_revision')
                ->where('address_book_revision.slug = :slug')
                ->setParameter('slug', $slug)
                ->getQuery()
                ->getResult(Query::HYDRATE_OBJECT)
        );

        $eventRevisions->filter(
            static function (AddressBookRevision $addressBookRevision) use ($referenceDateTime) {
                return $addressBookRevision->isActive($referenceDateTime);
            }
        );

        if ($eventRevisions->count() === 1) {
            return $eventRevisions->first();
        }

        if ($eventRevisions->count() < 1) {
            return null;
        }

        throw new NonUniqueResultException('Nalezeno více událostí se zadaným identifikátorem'.($slug ? ' '.$slug : '').'.');
    }
}
