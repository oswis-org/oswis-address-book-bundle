<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace Zakjakub\OswisAddressBookBundle\Controller;

use LogicException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Zakjakub\OswisAddressBookBundle\Entity\Organization;
use Zakjakub\OswisAddressBookBundle\Provider\OswisAddressBookSettingsProvider;
use Zakjakub\OswisAddressBookBundle\Service\OrganizationService;

class AboutUsController extends AbstractController
{
    public OrganizationService $organizationService;

    public OswisAddressBookSettingsProvider $addressBookSettings;

    public function __construct(OrganizationService $organizationService, OswisAddressBookSettingsProvider $addressBookSettings)
    {
        $this->addressBookSettings = $addressBookSettings;
        $this->organizationService = $organizationService;
    }

    /**
     * @return Response
     * @throws LogicException
     */
    public function aboutUsProfiles(): Response
    {
        return $this->render('@ZakjakubOswisAddressBook/web/parts/about-us-profiles.html.twig', ['organization' => $this->getAboutUsOrganization()]);
    }

    /**
     * Organization that is showed on web.
     */
    public function getAboutUsOrganization(): ?Organization
    {
        $organization = null;
        if (null !== $this->addressBookSettings->getPrimary()) {
            $organization = $this->organizationService->getRepository()->findOneBy(['slug' => $this->addressBookSettings->getPrimary()]);
        }
        $organization ??= $this->organizationService->getRepository()->findBy([], ['id' => 'ASC'])[0];

        return $organization;
    }

    /**
     * @return Response
     * @throws LogicException
     */
    public function aboutUs(): Response
    {
        return $this->render(
            '@ZakjakubOswisAddressBook/web/pages/about-us.html.twig',
            [
                'organization' => $this->getAboutUsOrganization(),
                'title'        => 'O nás',
            ]
        );
    }
}