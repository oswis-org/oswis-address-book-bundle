<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactImage;
use OswisOrg\OswisCoreBundle\Form\AbstractClass\AbstractImageType;

class ContactImageType extends AbstractImageType
{
    public static function getFileClassName(): string
    {
        return ContactImage::class;
    }

    public function getBlockPrefix(): string
    {
        return 'oswis_address_book_contact_image';
    }
}
