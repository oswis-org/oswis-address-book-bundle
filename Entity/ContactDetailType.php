<?php

namespace Zakjakub\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Zakjakub\OswisCoreBundle\Entity\Nameable;
use Zakjakub\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use Zakjakub\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use Zakjakub\OswisCoreBundle\Traits\Entity\TypeTrait;

/**
 * Class ContactType
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_detail_type")
 * @ApiResource(
 *   attributes={
 *     "filters"={"search"},
 *     "access_control"="is_granted('ROLE_MANAGER')"
 *   },
 *   collectionOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_contact_detail_types_get"}},
 *     },
 *     "post"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_contact_detail_types_post"}}
 *     }
 *   },
 *   itemOperations={
 *     "get"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "normalization_context"={"groups"={"address_book_contact_detail_type_get"}},
 *     },
 *     "put"={
 *       "access_control"="is_granted('ROLE_MANAGER')",
 *       "denormalization_context"={"groups"={"address_book_contact_detail_type_put"}}
 *     },
 *     "delete"={
 *       "access_control"="is_granted('ROLE_ADMIN')",
 *       "denormalization_context"={"groups"={"address_book_contact_detail_type_delete"}}
 *     }
 *   }
 * )
 * @ApiFilter(OrderFilter::class)
 * @Searchable({
 *     "id",
 *     "appUser.username",
 *     "appUser.description",
 *     "appUser.note"
 * })
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact")
 */
class ContactDetailType
{
    public const TYPE_EMAIL = 'email';
    public const TYPE_URL = 'url';
    public const TYPE_PHONE = 'phone';
    public const TYPE_SOCIAL = 'social';
    public const TYPE_MESSENGER = 'messenger';
    public const TYPE_VOIP = 'voip';

    public const ALLOWED_TYPES = [
        self::TYPE_URL       => ['name' => 'URL'],
        self::TYPE_EMAIL     => ['name' => 'E-mail'],
        self::TYPE_PHONE     => ['name' => 'Telefon'],
        self::TYPE_SOCIAL    => ['name' => 'Profil na sociální síti'],
        self::TYPE_MESSENGER => ['name' => 'Internetový komunikátor'],
        self::TYPE_VOIP      => ['name' => 'Internetová telefonie'],
    ];

    use BasicEntityTrait;
    use NameableBasicTrait;
    use TypeTrait;

    /**
     * @var Collection|null $contacts Contacts of this type
     * @Doctrine\ORM\Mapping\OneToMany(
     *     targetEntity="Zakjakub\OswisAddressBookBundle\Entity\ContactDetail",
     *     mappedBy="contactType",
     *     cascade={"all"},
     *     orphanRemoval=true
     * )
     */
    protected $contacts;

    /**
     * @var string|null $contactSchema Schema of type of contact
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected $contactSchema;

    /**
     * Show in address book preview?
     *
     * @var bool|null $showInPreview
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected $showInPreview;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $formLabel;

    /**
     * @var string|null
     * @ORM\Column(type="string", nullable=true)
     */
    protected $formHelp;

    /**
     * ContactDetailType constructor.
     *
     * @param Nameable|null $nameable
     * @param string|null   $schema
     * @param bool|null     $showInPreview
     * @param string|null   $type
     *
     * @param string|null   $formLabel
     * @param string|null   $formHelp
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?string $schema = null,
        ?bool $showInPreview = null,
        ?string $type = null,
        ?string $formLabel = null,
        ?string $formHelp = null
    ) {
        $this->contacts = new ArrayCollection();
        $this->setFieldsFromNameable($nameable);
        $this->setContactSchema($schema);
        $this->setShowInPreview($showInPreview);
        $this->setType($type);
        $this->setFormLabel($formLabel);
        $this->setFormHelp($formHelp);
    }

    final public static function getAllowedTypesDefault(): array
    {
        return [
            self::TYPE_URL,
            self::TYPE_EMAIL,
            self::TYPE_PHONE,
            self::TYPE_SOCIAL,
            self::TYPE_MESSENGER,
            self::TYPE_VOIP,
        ];
    }

    public static function getAllowedTypesCustom(): array
    {
        return [];
    }

    /**
     * @return string|null
     */
    final public function getFormLabel(): ?string
    {
        return $this->formLabel;
    }

    /**
     * @param string|null $formLabel
     */
    final public function setFormLabel(?string $formLabel): void
    {
        $this->formLabel = $formLabel;
    }

    /**
     * @return string|null
     */
    final public function getFormHelp(): ?string
    {
        return $this->formHelp;
    }

    /**
     * @param string|null $formHelp
     */
    final public function setFormHelp(?string $formHelp): void
    {
        $this->formHelp = $formHelp;
    }

    /**
     * @return bool|null
     */
    final public function getShowInPreview(): ?bool
    {
        return $this->showInPreview;
    }

    /**
     * @param bool|null $showInPreview
     */
    final public function setShowInPreview(?bool $showInPreview): void
    {
        $this->showInPreview = $showInPreview;
    }

    final public function addContact(?ContactDetail $contact): void
    {
        if ($contact && !$this->contacts->contains($contact)) {
            $this->contacts->add($contact);
            $contact->setContactType($this);
        }
    }

    final public function removeContact(?ContactDetail $contact): void
    {
        if ($contact && $this->contacts->removeElement($contact)) {
            $contact->setContactType(null);
        }
    }

    /**
     * @return Collection
     */
    final public function getContacts(): Collection
    {
        return $this->contacts;
    }

    final public function getFormatted(string $value, ?string $description): string
    {
        /** @noinspection UnknownInspectionInspection */
        /** @noinspection HtmlUnknownTag */
        return strtr($this->getContactSchema(), array('$<value>' => $value, '$<description>' => $description));
    }

    /**
     * Get schema of contact detail.
     * @return string
     */
    final public function getContactSchema(): ?string
    {
        return $this->contactSchema;
    }

    /**
     * Set schema of contact detail.
     *
     * @param string $contactSchema
     */
    final public function setContactSchema(?string $contactSchema): void
    {
        $this->contactSchema = $contactSchema;
    }
}
