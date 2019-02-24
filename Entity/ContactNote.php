<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * Class PersonNote
 *
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_note")
 * @ApiResource()
 */
class ContactNote
{

    use BasicEntityTrait;

    /**
     * Content of note.
     * @var string $content
     * @Doctrine\ORM\Mapping\Column(type="text")
     */
    private $content;

    /**
     * Contact that this not belongs to.
     * @var AbstractContact|null $contact Contact, that this note belongs to
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="internalNotes"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private $contact;

    /**
     * ContactNote constructor.
     */
    public function __construct()
    {
        $this->content = '';
    }

    /**
     * @return string
     */
    final public function getContent(): string
    {
        return $this->content ?? '';
    }

    /**
     * @param string $content
     */
    final public function setContent(?string $content): void
    {
        $this->content = $content;
    }

    /**
     * @return AbstractContact
     */
    final public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    /**
     * @param AbstractContact|null $contact
     */
    final public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $this->contact !== $contact) {
            $this->contact->removeInternalNote($this);
        }
        if ($contact && $this->contact !== $contact) {
            $this->contact = $contact;
            $contact->addInternalNote($this);
        }
    }

    final public function __toString(): string
    {
        return $this->content;
    }
}
