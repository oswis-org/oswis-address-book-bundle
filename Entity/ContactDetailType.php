<?php
/**
 * @noinspection PhpUnused
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Core\Annotation\ApiFilter;
use ApiPlatform\Core\Annotation\ApiResource;
use ApiPlatform\Core\Bridge\Doctrine\Orm\Filter\OrderFilter;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use OswisOrg\OswisCoreBundle\Entity\Nameable;
use OswisOrg\OswisCoreBundle\Filter\SearchAnnotation as Searchable;
use OswisOrg\OswisCoreBundle\Interfaces\BasicEntityInterface;
use OswisOrg\OswisCoreBundle\Traits\Entity\BasicEntityTrait;
use OswisOrg\OswisCoreBundle\Traits\Entity\NameableBasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Entity\TypeTrait;

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
class ContactDetailType implements BasicEntityInterface
{
    public const TYPE_EMAIL = 'email';
    public const TYPE_URL = 'url';
    public const TYPE_PHONE = 'telephone';
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
     * @var string|null $contactSchema Schema of type of contact
     * @Doctrine\ORM\Mapping\Column(type="string", nullable=true)
     */
    protected ?string $contactSchema = null;

    /**
     * Show in address book preview?
     * @Doctrine\ORM\Mapping\Column(type="boolean", nullable=true)
     */
    protected ?bool $showInPreview = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $formLabel = null;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    protected ?string $formHelp = null;

    /**
     * ContactDetailType constructor.
     *
     * @param Nameable|null $nameable
     * @param string|null   $schema
     * @param bool|null     $showInPreview
     * @param string|null   $type
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
        $this->setFieldsFromNameable($nameable);
        $this->setContactSchema($schema);
        $this->setShowInPreview($showInPreview);
        $this->setType($type);
        $this->setFormLabel($formLabel);
        $this->setFormHelp($formHelp);
    }

    public static function getAllowedTypesDefault(): array
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

    public function getFormLabel(): ?string
    {
        return $this->formLabel;
    }

    public function setFormLabel(?string $formLabel): void
    {
        $this->formLabel = $formLabel;
    }

    public function getFormHelp(): ?string
    {
        return $this->formHelp;
    }

    public function setFormHelp(?string $formHelp): void
    {
        $this->formHelp = $formHelp;
    }

    public function getShowInPreview(): ?bool
    {
        return $this->showInPreview;
    }

    public function setShowInPreview(?bool $showInPreview): void
    {
        $this->showInPreview = $showInPreview;
    }

    /**
     * @param string      $value
     * @param string|null $description
     *
     * @param string|null $name
     *
     * @return string
     * @noinspection UnknownInspectionInspection
     * @noinspection HtmlUnknownTag
     */
    public function getFormatted(string $value, ?string $description, ?string $name = null): string
    {
        return strtr($this->getContactSchema(), ['$<value>' => $value, '$<description>' => $description, '$<name>' => $name]);
    }

    /**
     * Get schema of contact detail.
     * @return string
     */
    public function getContactSchema(): ?string
    {
        return $this->contactSchema;
    }

    /**
     * Set schema of contact detail.
     *
     * @param string $contactSchema
     */
    public function setContactSchema(?string $contactSchema): void
    {
        $this->contactSchema = $contactSchema;
    }
}
