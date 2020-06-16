<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Entity;

use OswisOrg\OswisAddressBookBundle\Entity\AbstractClass\AbstractContact;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\Publicity;
use OswisOrg\OswisCoreBundle\Exceptions\InvalidTypeException;
use OswisOrg\OswisCoreBundle\Interfaces\Common\BasicInterface;
use OswisOrg\OswisCoreBundle\Traits\Common\BasicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\EntityPublicTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\PriorityTrait;
use OswisOrg\OswisCoreBundle\Traits\Common\TypeTrait;

/**
 * @Doctrine\ORM\Mapping\Entity()
 * @Doctrine\ORM\Mapping\Table(name="address_book_contact_image_connection")
 * @Doctrine\ORM\Mapping\Cache(usage="NONSTRICT_READ_WRITE", region="address_book_contact_image")
 */
class ContactImageConnection implements BasicInterface
{
    use BasicTrait;
    use TypeTrait;
    use PriorityTrait;
    use EntityPublicTrait;

    /**
     * @Doctrine\ORM\Mapping\OneToOne(
     *     targetEntity="OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage", cascade={"all"}, fetch="EAGER", orphanRemoval=true
     * )
     */
    protected ?ContactImage $image = null;

    /**
     * @param ContactImage|null $image
     * @param string|null       $type
     * @param int|null          $priority
     * @param Publicity|null    $publicity
     *
     * @throws InvalidTypeException
     */
    public function __construct(?ContactImage $image = null, ?string $type = null, ?int $priority = null, ?Publicity $publicity = null)
    {
        $this->setType($type);
        $this->setPriority($priority);
        $this->setFieldsFromPublicity($publicity);
        $this->setImage($image);
    }

    public function getImage(): ?ContactImage
    {
        return $this->image;
    }

    public function setImage(?ContactImage $image): void
    {
        $this->image = $image;
    }
}
