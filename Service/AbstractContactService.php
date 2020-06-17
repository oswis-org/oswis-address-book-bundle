<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use function assert;

class AbstractContactService
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
