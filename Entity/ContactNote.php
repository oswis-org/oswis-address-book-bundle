<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_note")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactNote implements BasicInterface
{
    use BasicTrait;

    /**
     * Can be showed on website etc.
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $public = null;

    /**
     * Content of note.
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    protected ?string $content = null;

    /**
     * @Doctrine\ORM\Mapping\ManyToOne(targetEntity="OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact", inversedBy="notes")
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    protected ?AbstractContact $contact = null;

    public function __construct(?string $content = null, ?bool $public = null, ?AbstractContact $contact = null)
    {
        $this->setContact($contact);
        $this->setContent($content);
        $this->setPublic($public);
    }

    public function getContact(): ?AbstractContact
    {
        return $this->contact;
    }

    public function setContact(?AbstractContact $contact): void
    {
        if ($this->contact && $contact !== $this->contact) {
            $this->contact->removeNote($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addNote($this);
        }
    }

    public function getPublic(): ?bool
    {
        return $this->public;
    }

    public function setPublic(?bool $public): void
    {
        $this->public = $public;
    }

    public function isPublic(): ?bool
    {
        return $this->public;
    }

    public function __toString(): string
    {
        return $this->getContent() ?? '';
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
