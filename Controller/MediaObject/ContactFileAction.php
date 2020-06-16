<?php

namespace OswisOrg\OswisAddressBookBundle\Controller\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisCoreBundle\Controller\AbstractClass\AbstractFileAction;

final class ContactFileAction extends AbstractFileAction
{
    public static function getFileClassName(): string
    {
        return ContactFile::class;
    }

    public static function getFileNewInstance(): ContactFile
    {
        return new ContactFile();
    }
}
