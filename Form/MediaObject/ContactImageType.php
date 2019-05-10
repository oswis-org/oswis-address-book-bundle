<?php

namespace Zakjakub\OswisAddressBookBundle\Form\MediaObject;

use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;
use Zakjakub\OswisCoreBundle\Form\AbstractClass\AbstractImageType;

final class ContactImageType extends AbstractImageType
{

    /**
     * @return string
     */
    public static function getImageClassName(): string
    {
        return ContactImage::class;
    }

}
