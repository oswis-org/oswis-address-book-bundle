<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use Psr\Log\LoggerInterface;

class PersonService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
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

    public function updateActiveRevisions(): void
    {
    }
}
