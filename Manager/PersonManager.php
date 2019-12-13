<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Manager;

use Doctrine\ORM\EntityManagerInterface;
use Zakjakub\OswisAddressBookBundle\Entity\Person;

class PersonManager
{
    protected EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
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
