<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use OswisOrg\OswisAddressBookBundle\Repository\PersonRepository;
use Psr\Log\LoggerInterface;

class PersonService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    protected PersonRepository $personRepository;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, PersonRepository $personRepository)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->personRepository = $personRepository;
    }

    public function create(Person $person): ?Person
    {
        try {
            $this->em->persist($person);
            $this->em->flush();
            $infoMessage = 'Created person: '.$person->getId().' '.$person->getName().'.';
            $this->logger->info($infoMessage);

            return $person;
        } catch (Exception $e) {
            $this->logger->info('ERROR: Person not created: '.$e->getMessage());

            return null;
        }
    }

    public function getRepository(): PersonRepository
    {
        return $this->personRepository;
    }

    public function updateActiveRevisions(): void
    {
    }
}
