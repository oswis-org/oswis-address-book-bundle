<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Service;

use DateTimeInterface;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Person;
use Zakjakub\OswisCoreBundle\Entity\AppUser;

class PersonService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function create(
        ?string $fullName = null,
        ?string $description = null,
        ?DateTimeInterface $birthDate = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $positions = null,
        ?Collection $personSkillConnections = null,
        ?Collection $addressBooks = null,
        ?AppUser $appUser = null
    ): ?Person {
        try {
            $entity = new Person($fullName, $description, $birthDate, $type, $notes, $contactDetails, $addresses, $positions, $personSkillConnections, $addressBooks, $appUser);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created person: '.$entity->getId().' '.$entity->getContactName().'.';
            $this->logger->info($infoMessage);

            return $entity;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Person not created: '.$e->getMessage());

            return null;
        }
    }

    public function updateActiveRevisions(): void
    {
        foreach ($this->em->getRepository(Person::class)->findAll() as $person) {
            assert($person instanceof Person);
            $person->destroyRevisions();
            $this->em->persist($person);
        }
        $this->em->flush();
    }
}
