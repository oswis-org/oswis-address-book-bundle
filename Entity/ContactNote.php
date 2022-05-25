<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;

#[Entity]
#[Table(name: 'address_book_contact_note')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact')]
class ContactNote implements BasicInterface
{
    use BasicTrait;

    /** Can be showed on website etc. */
    #[Column(type: 'boolean', nullable: true)]
    protected ?bool $public = null;

    /** Content of note. */
    #[Column(type: 'text', nullable: true)]
    protected ?string $content = null;

    #[ManyToOne(targetEntity: AbstractContact::class, inversedBy: 'notes')]
    #[JoinColumn(name: 'contact_id', referencedColumnName: 'id')]
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
        return $this->getPublic();
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
