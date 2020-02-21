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
use Zakjakub\OswisCoreBundle\Exceptions\OswisNotFoundException;

class OrganizationController extends AbstractController
{
    public OrganizationService $organizationService;

    public OswisAddressBookSettingsProvider $addressBookSettings;

    public function __construct(OrganizationService $organizationService, OswisAddressBookSettingsProvider $addressBookSettings)
    {
        $this->addressBookSettings = $addressBookSettings;
        $this->organizationService = $organizationService;
    }

    /**
     * @param string|null $slug
     *
     * @return Response
     * @throws LogicException
     */
    public function showOrganizationProfiles(?string $slug = null): Response
    {
        return $this->render(
            '@ZakjakubOswisAddressBook/web/parts/organization-person-profiles.html.twig',
            ['organization' => $this->getOrganization($slug)]
        );
    }

    public function getOrganization(?string $slug = null): ?Organization
    {
        $organization = $this->getDefaultOrganization();
        if (!empty($slug)) {
            $organization = $this->organizationService->getRepository()->findOneBy(['slug' => $slug, 'publicOnWeb' => true,]);
        }

        return $organization;
    }

    public function getDefaultOrganization(): ?Organization
    {
        $organization = null;
        if (null !== $this->addressBookSettings->getPrimary()) {
            $organization = $this->organizationService->getRepository()->findOneBy(
                [
                    'slug'        => $this->addressBookSettings->getPrimary(),
                    'publicOnWeb' => true,
                ]
            );
        }
        $organization ??= $this->organizationService->getRepository()->findBy(
                ['publicOnWeb' => true],
                ['id' => 'ASC']
            )[0] ?? null;

        return $organization;
    }

    /**
     * @param string|null $slug
     *
     * @return Response
     * @throws LogicException
     * @throws OswisNotFoundException
     */
    public function showOrganizationPage(?string $slug = null): Response
    {
        $organization = empty($slug) ? $this->getDefaultOrganization() : $this->getOrganization($slug);
        if (!empty($slug) && $this->getDefaultOrganization() && $slug === $this->getDefaultOrganization()->getSlug()) {
            $this->redirectToRoute('zakjakub_oswis_address_book_organization');
        }
        if (null === $organization) {
            throw new OswisNotFoundException('Organizace nenalezena.');
        }

        return $this->render(
            '@ZakjakubOswisAddressBook/web/pages/organization.html.twig',
            [
                'organization' => $organization,
                'title'        => $this->isDefaultOrganization($organization) ? 'O nás' : $organization->getName(),
            ]
        );
    }

    public function isDefaultOrganization(?Organization $organization): bool
    {
        return $organization && $this->getDefaultOrganization() === $organization;
    }
}