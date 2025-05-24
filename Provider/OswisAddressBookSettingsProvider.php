<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Provider;

/**
 * Provider of settings for address book module of OSWIS.
 */
class OswisAddressBookSettingsProvider
{
    /** @var array{organization?: string}|null $primary */
    protected ?array $primary = null;

    /** @param array{organization?: string}|null $primary */
    public function __construct(?array $primary)
    {
        $this->primary = $primary;
    }

    public function getOrganization(): ?string
    {
        return $this->getPrimary() ? ($this->getPrimary()['organization']) : null;
    }

    /**
     * @return array{organization?: string}|null
     */
    public function getPrimary(): ?array
    {
        return $this->primary;
    }
}
