<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Service;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisAddressBookBundle\Entity\ContactDetail;
use OswisOrg\OswisAddressBookBundle\Entity\Person;
use OswisOrg\OswisAddressBookBundle\Entity\Position;
use function assert;

class AbstractContactService
{
    protected EntityManagerInterface $em;

    protected ContactDetailTypeService $contactDetailTypeService;

    public function __construct(EntityManagerInterface $em, ContactDetailTypeService $contactDetailTypeService)
    {
        $this->em = $em;
        $this->contactDetailTypeService = $contactDetailTypeService;
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

    /**
     * @param AbstractContact|null $contact
     *
     * @param array                $detailTypeSlugs
     *
     * @return AbstractContact
     * @throws InvalidArgumentException
     */
    public function getContact(?AbstractContact $contact = null, array $detailTypeSlugs = []): AbstractContact
    {
        if (null !== $contact) {
            return $contact;
        }
        $positions = new ArrayCollection([new Position(null, null, null, Position::TYPE_STUDENT)]);
        $contactDetails = new ArrayCollection();
        foreach ($detailTypeSlugs as $detailTypeSlug) {
            $contactDetails->add(new ContactDetail($this->contactDetailTypeService->getBySlug($detailTypeSlug)));
        }

        return new Person(null, null, $contactDetails, null, $positions);
    }
}
