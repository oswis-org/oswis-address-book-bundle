<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Provider;

/**
 * Provider of settings for address book module of OSWIS.
 */
class OswisAddressBookSettingsProvider
{
    protected ?string $organization = null;

    public function __construct(?string $organization)
    {
        $this->organization = $organization;
    }

    final public function getOrganization(): ?string
    {
        return $this->organization;
    }
}
