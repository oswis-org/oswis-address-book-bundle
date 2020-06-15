<?php

namespace OswisOrg\OswisAddressBookBundle\Controller\MediaObject;

use OswisOrg\OswisAddressBookBundle\Entity\MediaObject\ContactFile;
use OswisOrg\OswisCoreBundle\Controller\AbstractClass\AbstractFileAction;
use OswisOrg\OswisCoreBundle\Entity\AbstractClass\AbstractFile;

final class ContactFileAction extends AbstractFileAction
{
    public static function getFileClassName(): string
    {
        return ContactFile::class;
    }

    public static function getFileNewInstance(): AbstractFile
    {
        return new ContactFile();
    }
}
