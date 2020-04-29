<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 * @noinspection PhpUnused
 */

namespace OswisOrg\OswisAddressBookBundle\Controller;

use OswisOrg\OswisAddressBookBundle\Entity\Organization;
use OswisOrg\OswisAddressBookBundle\Provider\OswisAddressBookSettingsProvider;
use OswisOrg\OswisAddressBookBundle\Service\OrganizationService;
use OswisOrg\OswisCoreBundle\Exceptions\OswisNotFoundException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OrganizationController extends AbstractController
{
    public OrganizationService $organizationService;

    public OswisAddressBookSettingsProvider $addressBookSettings;

    public function __construct(
        OrganizationService $organizationService,
        OswisAddressBookSettingsProvider $addressBookSettings
    ) {
        $this->addressBookSettings = $addressBookSettings;
        $this->organizationService = $organizationService;
    }

    /**
     * @param string|null $slug
     *
     * @return Response
     */
    public function showOrganizationProfiles(?string $slug = null): Response
    {
        return $this->render(
            '@OswisOrgOswisAddressBook/web/parts/organization-person-profiles.html.twig',
            ['organization' => $this->getOrganization($slug)]
        );
    }

    public function getOrganization(?string $slug = null): ?Organization
    {
        $organization = $this->getDefaultOrganization();
        if (!empty($slug)) {
            $organization = $this->organizationService->getRepository()
                ->findOneBy(['slug' => $slug, 'publicOnWeb' => true,]);
        }

        return $organization;
    }

    public function getDefaultOrganization(): ?Organization
    {
        $organization = null;
        if (null !== $this->addressBookSettings->getPrimary()) {
            $organization = $this->organizationService->getRepository()
                ->findOneBy(
                    [
                        'slug'        => $this->addressBookSettings->getPrimary(),
                        'publicOnWeb' => true,
                    ]
                );
        }
        $organization ??= $this->organizationService->getRepository()
                ->findBy(
                    ['publicOnWeb' => true],
                    ['id' => 'ASC']
                )[0] ?? null;

        return $organization;
    }

    /**
     * @param string|null $slug
     *
     * @return Response
     * @throws OswisNotFoundException
     */
    public function showOrganizationPage(?string $slug = null): Response
    {
        $defaultOrganization = $this->getDefaultOrganization();
        if (empty($slug) && null !== $defaultOrganization) {
            return $this->redirectToRoute('oswis_org_oswis_address_book_organization', ['slug' => $defaultOrganization->getSlug()]);
        }
        $organization = $this->getOrganization($slug);
        if (null === $organization) {
            throw new OswisNotFoundException('Organizace nenalezena.');
        }

        return $this->render(
            '@OswisOrgOswisAddressBook/web/pages/organization.html.twig',
            [
                'organization' => $organization,
                'title'        => ($this->isDefaultOrganization($organization) ? 'O nás :: ' : '').$organization->getName(),
                'breadcrumbs'  => $this->getBreadCrumbs(),
            ]
        );
    }

    public function isDefaultOrganization(?Organization $organization): bool
    {
        return $organization && $this->getDefaultOrganization() === $organization;
    }

    public function getBreadCrumbs(): array
    {
        return [
            [
                'url'   => $this->generateUrl('oswis_org_oswis_address_book_organization'),
                'title' => 'Organizace',
            ],
        ];
    }
}