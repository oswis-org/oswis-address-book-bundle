<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Extender;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Service\OrganizationService;
use OswisOrg\OswisCoreBundle\Entity\NonPersistent\SiteMapItem;
use OswisOrg\OswisCoreBundle\Interfaces\Web\SiteMapExtenderInterface;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Routing\Exception\MissingMandatoryParametersException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class AddressBookSitemapExtender implements SiteMapExtenderInterface
{
    public function __construct(
        protected UrlGeneratorInterface $urlGenerator,
        protected OrganizationService   $organizationService,
    )
    {
    }

    public function getItems(): Collection
    {
        $items = new ArrayCollection();
        foreach ($this->organizationService->getRepository()->findBy(['publicOnWeb' => true]) as $organization) {
            if (!($organization instanceof Organization)) {
                continue;
            }
            try {
                $items->add(
                    new SiteMapItem(
                        $this->urlGenerator->generate(
                            'oswis_org_oswis_address_book_organization', ['slug' => $organization->getSlug()]
                        ), null, $organization->getUpdatedAt()
                    )
                );
            } catch (InvalidParameterException|RouteNotFoundException|MissingMandatoryParametersException) {
            }
        }
        try {
            $items->add(new SiteMapItem($this->urlGenerator->generate('oswis_org_oswis_address_book_about_us')));
        } catch (InvalidParameterException|MissingMandatoryParametersException|RouteNotFoundException) {
        }

        return $items;
    }
}
