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
use Zakjakub\OswisAddressBookBundle\Service\OrganizationService;

class AboutUsController extends AbstractController
{
    public OrganizationService $organizationService;

    public function __construct(OrganizationService $organizationService)
    {
        $this->organizationService = $organizationService;
    }

    /**
     * @return Response
     * @throws LogicException
     */
    public function aboutUsProfiles(): Response
    {
        return $this->render(
            '@ZakjakubOswisAddressBook/web/parts/about-us-profiles.html.twig',
            ['organization' => $this->getAboutUsOrganization()],
            );
    }

    /**
     * Organization that is showed on web - REIMPLEMENT IT IN APP!
     * @return Organization|null Main organization.
     */
    public function getAboutUsOrganization(): ?Organization
    {
        return $this->organizationService->getRepository()->findBy([], ['id' => 'ASC'])[0] ?? null;
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