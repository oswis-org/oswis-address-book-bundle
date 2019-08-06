<?php

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\AddressBook\AddressBook;
use Zakjakub\OswisCoreBundle\Entity\Nameable;

class AddressBookManager
{
    /**
     * @var EntityManagerInterface
     */
    protected $em;

    /**
     * @var LoggerInterface|null
     */
    protected $logger;

    public function __construct(
        EntityManagerInterface $em,
        ?LoggerInterface $logger = null
    ) {
        $this->em = $em;
        $this->logger = $logger;
    }

    final public function create(
        ?Nameable $nameable = null
    ): AddressBook {
        try {
            $em = $this->em;
            $entity = new AddressBook(
                $nameable
            );
            $em->persist($entity);
            $em->flush();
            $infoMessage = 'Created address book (by manager): '.$entity->getId().', '.$entity->getName().'.';
            $this->logger ? $this->logger->info($infoMessage) : null;

            return $entity;
        } catch (Exception $e) {
            $this->logger ? $this->logger->info('ERROR: Address book not created (by manager): '.$e->getMessage()) : null;

            return null;
        }
    }

    final public function updateActiveRevisions(): void
    {
        $addressBooks = $this->em->getRepository(AddressBook::class)->findAll();
        foreach ($addressBooks as $addressBook) {
            assert($addressBook instanceof AddressBook);
            $addressBook->updateActiveRevision();
            $this->em->persist($addressBook);
        }
        $this->em->flush();
    }

}
