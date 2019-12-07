<?php /** @noinspection PhpUnused */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_note")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactNote
{
    use BasicEntityTrait;

    /**
     * Can be showed on website etc.
     * @var bool|null
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $public = null;

    /**
     * Content of note.
     * @var string|null $content
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    private ?string $content = null;

    /**
     * Contact that this not belongs to.
     * @var AbstractContact|null $contact Contact, that this note belongs to
     * @Doctrine\ORM\Mapping\ManyToOne(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact",
     *     inversedBy="notes"
     * )
     * @Doctrine\ORM\Mapping\JoinColumn(name="contact_id", referencedColumnName="id")
     */
    private ?AbstractContact $contact = null;

    /**
     * ContactNote constructor.
     *
     * @param string|null          $content
     * @param AbstractContact|null $contact
     * @param bool|null            $public
     */
    public function __construct(
        ?string $content = null,
        ?AbstractContact $contact = null,
        ?bool $public = null
    ) {
        $this->setContact($contact);
        $this->setContent($content);
        $this->setPublic($public);
    }

    /**
     * @return bool|null
     */
    final public function getPublic(): ?bool
    {
        return $this->public;
    }

    /**
     * @param bool|null $public
     */
    final public function setPublic(?bool $public): void
    {
        $this->public = $public;
    }

    /**
     * @return bool|null
     */
    final public function isPublic(): ?bool
    {
        return $this->public;
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
            $this->contact->removeNote($this);
        }
        $this->contact = $contact;
        if ($contact && $this->contact !== $contact) {
            $contact->addNote($this);
        }
    }

    final public function __toString(): string
    {
        return $this->getContent() ?? '';
    }

    /**
     * @return string
     */
    final public function getContent(): ?string
    {
        return $this->content;
    }

    /**
     * @param string $content
     */
    final public function setContent(?string $content): void
    {
        $this->content = $content;
    }
}
