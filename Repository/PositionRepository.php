<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Repository;

use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Entity\Position;

class PositionRepository extends ServiceEntityRepository
{
    public const string CRITERIA_ID = 'id';

    public const string CRITERIA_ORG = 'organization';

    public const string CRITERIA_ORG_RECURSIVE_DEPTH = 'orgRecursiveDepth';

    public const string CRITERIA_POSITION_TYPE = 'positionType';

    public const string CRITERIA_ONLY_ACTIVE = 'onlyActive';

    public const string CRITERIA_ONLY_CONTACT_PERSON = 'onlyContactPerson';

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Position::class);
    }

    public function findOneBy(array $criteria, ?array $orderBy = null): ?Position
    {
        $result = parent::findOneBy($criteria, $orderBy);

        return $result instanceof Position ? $result : null;
    }

    public function getPositions(array $opts = [], ?int $limit = null, ?int $offset = null): Collection
    {
        $result = $this->getPositionsQueryBuilder($opts, $limit, $offset)->getQuery()->getResult();
        assert(is_array($result));

        return new ArrayCollection($result);
    }

    public function getPositionsQueryBuilder(array $opts = [], ?int $limit = null, ?int $offset = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('position');
        $this->setSuperEventQuery($queryBuilder, $opts);
        $this->setIdQuery($queryBuilder, $opts);
        $this->setPositionTypeQuery($queryBuilder, $opts);
        $this->setOnlyActiveQuery($queryBuilder, $opts);
        $this->setOnlyContactPersonQuery($queryBuilder, $opts);
        $this->setLimit($queryBuilder, $limit, $offset);
        $this->setOrderBy($queryBuilder);

        return $queryBuilder;
    }

    private function setSuperEventQuery(QueryBuilder $queryBuilder, array $opts = []): void
    {
        if (!empty($opts[self::CRITERIA_ORG]) && $opts[self::CRITERIA_ORG] instanceof Organization) {
            $eventQuery = ' position.organization = :organization_id ';
            $queryBuilder->leftJoin('position.organization', 'o0');
            $recursiveDepth = !empty($opts[self::CRITERIA_ORG_RECURSIVE_DEPTH]) ? $opts[self::CRITERIA_ORG_RECURSIVE_DEPTH] : 0;
            for ($i = 0; $i < $recursiveDepth; $i++) {
                $j = $i + 1;
                $queryBuilder->leftJoin("o$i.parentOrganization", "o$j");
                $eventQuery .= " OR o$j = :organization_id ";
            }
            $queryBuilder->andWhere($eventQuery)->setParameter('organization_id', $opts[self::CRITERIA_ORG]->getId());
        }
    }

    private function setIdQuery(QueryBuilder $queryBuilder, array $opts = []): void
    {
        if (!empty($opts[self::CRITERIA_ID])) {
            $queryBuilder->andWhere(' position.id = :id ')->setParameter('id', $opts[self::CRITERIA_ID]);
        }
    }

    private function setPositionTypeQuery(QueryBuilder $queryBuilder, array $opts = []): void
    {
        if (!empty($opts[self::CRITERIA_POSITION_TYPE]) && is_string($opts[self::CRITERIA_POSITION_TYPE])) {
            $queryBuilder->andWhere('position.type = :type_string');
            $queryBuilder->setParameter('type_string', $opts[self::CRITERIA_POSITION_TYPE]);
        }
    }

    private function setOnlyActiveQuery(QueryBuilder $queryBuilder, array $opts = []): void
    {
        if (!empty($opts[self::CRITERIA_ONLY_ACTIVE])) {
            $startQuery = ' (position.startDateTime IS NULL) OR (:now > position.startDateTime) ';
            $endQuery = ' (position.endDateTime IS NULL) OR (:now < position.endDateTime) ';
            $queryBuilder->andWhere($startQuery)->andWhere($endQuery)->setParameter('now', new DateTime());
        }
    }

    private function setOnlyContactPersonQuery(QueryBuilder $queryBuilder, array $opts = []): void
    {
        if (true === (bool)($opts[self::CRITERIA_ONLY_CONTACT_PERSON] ?? null)) {
            $queryBuilder->andWhere('position.contactPerson = true');
        }
    }

    private function setLimit(QueryBuilder $queryBuilder, ?int $limit = null, ?int $offset = null): void
    {
        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }
        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }
    }

    private function setOrderBy(QueryBuilder $queryBuilder): void
    {
        $queryBuilder->addOrderBy('position.id', 'ASC');
    }
}
