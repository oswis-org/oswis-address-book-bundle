<?php

namespace Zakjakub\OswisAddressBookBundle\Controller;

use Zakjakub\OswisAddressBookBundle\Entity\ContactImage;

final class ContactImageAction
{

    /**
     * @return string
     */
    public static function getImageClassName(): string
    {
        return ContactImage::class;
    }

}
