<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisCoreBundle\Form\AbstractClass\AbstractFileType;

class ContactFileType extends AbstractFileType
{
    public static function getFileClassName(): string
    {
        return ContactFile::class;
    }

    public function getBlockPrefix(): string
    {
        return 'oswis_address_book_contact_file';
    }
}
