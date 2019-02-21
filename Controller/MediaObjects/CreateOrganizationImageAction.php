<?php

namespace Zakjakub\OswisAccommodationBundle\Controller\MediaObjects;

use ApiPlatform\Core\Bridge\Symfony\Validator\Exception\ValidationException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Zakjakub\OswisAccommodationBundle\Entity\MediaObjects\OrganizationImage;
use Zakjakub\OswisAccommodationBundle\Form\MediaObjects\OrganizationImageType;

/**
 * Class CreateOrganizationImageAction
 * @package Zakjakub\OswisAccommodationBundle\Controller\MediaObjects
 */
final class CreateOrganizationImageAction
{
    /**
     * @var ValidatorInterface
     */
    private $validator;

    /**
     * @var RegistryInterface
     */
    private $doctrine;

    /**
     * @var FormFactoryInterface
     */
    private $factory;

    /**
     * CreateOrganizationImageAction constructor.
     *
     * @param RegistryInterface    $doctrine
     * @param FormFactoryInterface $factory
     * @param ValidatorInterface   $validator
     */
    public function __construct(
        RegistryInterface $doctrine,
        FormFactoryInterface $factory,
        ValidatorInterface $validator
    ) {
        $this->validator = $validator;
        $this->doctrine = $doctrine;
        $this->factory = $factory;
    }

    /**
     * @param Request $request
     *
     * @return OrganizationImage
     * @throws ValidationException
     * @throws \Symfony\Component\Form\Exception\LogicException
     * @throws \Symfony\Component\OptionsResolver\Exception\InvalidOptionsException
     * @IsGranted("ROLE_MANAGER")
     */
    public function __invoke(Request $request): OrganizationImage
    {
        $mediaObject = new OrganizationImage();

        $form = $this->factory->create(OrganizationImageType::class, $mediaObject);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->doctrine->getManager();
            $em->persist($mediaObject);
            $em->flush();

            // Prevent the serialization of the file property
            $mediaObject->file = null;

            return $mediaObject;
        }

        // This will be handled by API Platform and returns a validation error.
        throw new ValidationException($this->validator->validate($mediaObject));
    }
}
