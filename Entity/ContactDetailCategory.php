<?php
/**
 * @noinspection PropertyCanBePrivateInspection
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use ApiPlatform\Doctrine\Orm\Filter\OrderFilter;
use ApiPlatform\Metadata\ApiFilter;
use ApiPlatform\Metadata\ApiResource;
use ApiPlatform\Metadata\Delete;
use ApiPlatform\Metadata\Get;
use ApiPlatform\Metadata\GetCollection;
use ApiPlatform\Metadata\Post;
use ApiPlatform\Metadata\Put;
use Doctrine\ORM\Mapping\Cache;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\Table;
use OswisOrg\OswisAddressBookBundle\Repository\ContactDetailCategoryRepository;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Nameable;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Interfaces\Common\NameableInterface;
use OswisOrg\OswisCoreBundle\Interfaces\Common\TypeInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\NameableTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;

/**
 * @OswisOrg\OswisCoreBundle\Filter\SearchAnnotation({
 *     "id",
 *     "appUser.username",
 *     "appUser.description",
 *     "appUser.note"
 * })
 */
#[Entity(repositoryClass: ContactDetailCategoryRepository::class)]
#[Table(name: 'address_book_contact_detail_category')]
#[Cache(usage: 'NONSTRICT_READ_WRITE', region: 'address_book_contact_detail_category')]
#[ApiFilter(OrderFilter::class)]
#[ApiResource(
    operations: [
        new GetCollection(
            normalizationContext: ['groups' => ['entities_get', 'address_book_contact_detail_categories_get']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Post(
            denormalizationContext: ['groups' => ['entities_post', 'address_book_contact_detail_categories_post']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Get(
            normalizationContext: ['groups' => ['entity_get', 'address_book_contact_detail_category_get']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Put(
            denormalizationContext: ['groups' => ['entity_put', 'address_book_contact_detail_category_put']],
            security: "is_granted('ROLE_MANAGER')"
        ),
        new Delete(),
    ],
    filters: ['search'],
    security: "is_granted('ROLE_MANAGER')"
)]
class ContactDetailCategory implements NameableInterface, TypeInterface
{
    public const string TYPE_EMAIL = 'email';
    public const string TYPE_URL = 'url';
    public const string TYPE_PHONE = 'telephone';
    public const string TYPE_SOCIAL = 'social';
    public const string TYPE_MESSENGER = 'messenger';
    public const string TYPE_VOIP = 'voip';
    public const array ALLOWED_TYPES = [
        self::TYPE_URL,
        self::TYPE_EMAIL,
        self::TYPE_PHONE,
        self::TYPE_SOCIAL,
        self::TYPE_MESSENGER,
        self::TYPE_VOIP,
    ];
    public const array SPACELESS_TYPES
        = [
            self::TYPE_EMAIL,
            self::TYPE_URL,
            self::TYPE_PHONE,
        ];
    use NameableTrait;
    use TypeTrait;

    /**
     * @var string|null $contactSchema Schema of type of contact
     */
    #[Column(type: 'string', nullable: true)]
    protected ?string $contactSchema = null;

    /** Show in address book preview? */
    #[Column(type: 'boolean', nullable: true)]
    protected ?bool $showInPreview = null;

    #[Column(type: 'string', nullable: true)]
    protected ?string $formLabel = null;

    #[Column(type: 'string', nullable: true)]
    protected ?string $formHelp = null;

    #[Column(type: 'boolean', nullable: false)]
    protected bool $required = false;

    /**
     * ContactDetailType constructor.
     *
     * @param  Nameable|null  $nameable
     * @param  string|null  $schema
     * @param  bool|null  $showInPreview
     * @param  string|null  $type
     * @param  string|null  $formLabel
     * @param  string|null  $formHelp
     *
     * @param  bool  $required
     *
     * @throws InvalidTypeException
     */
    public function __construct(
        ?Nameable $nameable = null,
        ?string $schema = null,
        ?bool $showInPreview = null,
        ?string $type = null,
        ?string $formLabel = null,
        ?string $formHelp = null,
        bool $required = false
    ) {
        $this->setFieldsFromNameable($nameable);
        $this->setContactSchema($schema);
        $this->setShowInPreview($showInPreview);
        $this->setType($type);
        $this->setFormLabel($formLabel);
        $this->setFormHelp($formHelp);
        $this->setRequired($required);
    }

    public static function getAllowedTypesDefault(): array
    {
        return self::ALLOWED_TYPES;
    }

    public static function getAllowedTypesCustom(): array
    {
        return [];
    }

    public function isRequired(): bool
    {
        return $this->required;
    }

    public function setRequired(bool $required): void
    {
        $this->required = $required;
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
     * @param  string|null  $value
     * @param  string|null  $description
     * @param  string|null  $name
     *
     * @return string
     * @noinspection HtmlUnknownTag
     */
    public function getFormatted(?string $value = null, ?string $description = null, ?string $name = null): string
    {
        return empty($value) ? '' : strtr(''.$this->getContactSchema(), ['$<value>' => $value, '$<description>' => $description, '$<name>' => $name]);
    }

    /**
     * Get schema of contact detail.
     */
    public function getContactSchema(): ?string
    {
        return $this->contactSchema;
    }

    /**
     * Set schema of contact detail.
     *
     * @param  string|null  $contactSchema
     */
    public function setContactSchema(?string $contactSchema): void
    {
        $this->contactSchema = $contactSchema;
    }

    public function isSpaceless(): bool
    {
        return in_array($this->getType(), self::SPACELESS_TYPES, true);
    }
}
