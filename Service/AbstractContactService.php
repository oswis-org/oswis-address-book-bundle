<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Psr\Log\LoggerInterface;
use function assert;

class AbstractContactService
{
    protected EntityManagerInterface $em;

    protected LoggerInterface $logger;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
    }

    public function updateNames(): void
    {
        $contacts = $this->em->getRepository(AbstractContact::class)->findAll();
        foreach ($contacts as $contact) {
            assert($contact instanceof AbstractContact);
            $contact->updateName();
            $this->em->persist($contact);
        }
        $this->em->flush();
    }
}
