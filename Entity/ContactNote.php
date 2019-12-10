<?php /** @noinspection PhpUnused */

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
     * ContactNote constructor.
     *
     * @param string|null $content
     * @param bool|null   $public
     */
    public function __construct(
        ?string $content = null,
        ?bool $public = null
    ) {
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
