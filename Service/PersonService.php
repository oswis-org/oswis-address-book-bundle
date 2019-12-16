<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Service;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Person;

class PersonService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(
        ?string $fullName = null,
        ?string $description = null,
        ?DateTime $birthDate = null,
        ?string $type = null,
        ?Collection $notes = null,
        ?Collection $contactDetails = null,
        ?Collection $addresses = null,
        ?Collection $positions = null,
        ?Collection $personSkillConnections = null,
        ?Collection $addressBooks = null
    ): Person {
        try {
            $entity = new Person($fullName, $description, $birthDate, $type, $notes, $contactDetails, $addresses, $positions, $personSkillConnections, $addressBooks);
            $this->em->persist($entity);
            $this->em->flush();
            $infoMessage = 'Created organization: '.$entity->getId().' '.$entity->getContactName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Organization not created: '.$e->getMessage()) : null;

            return null;
        }
    }


    final public function updateActiveRevisions(): void
    {
        foreach ($this->em->getRepository(Person::class)->findAll() as $person) {
            assert($person instanceof Person);
            $person->destroyRevisions();
            $this->em->persist($person);
        }
        $this->em->flush();
    }
}
