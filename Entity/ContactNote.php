<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace Zakjakub\OswisAddressBookBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
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
     * @ORM\Column(type="boolean", nullable=true)
     */
    protected ?bool $public = null;

    /**
     * Content of note.
     * @Doctrine\ORM\Mapping\Column(type="text", nullable=true)
     */
    private ?string $content = null;

    public function __construct(?string $content = null, ?bool $public = null)
    {
        $this->setContent($content);
        $this->setPublic($public);
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
