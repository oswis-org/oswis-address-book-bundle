<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form;

use OswisOrg\OswisAddressBookBundle\Entity\ContactDetail;
use OswisOrg\OswisCoreBundle\Exceptions\OswisException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TelType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Regex;
use Symfony\Component\Validator\Exception\ConstraintDefinitionException;
use Symfony\Component\Validator\Exception\InvalidOptionsException;
use Symfony\Component\Validator\Exception\MissingOptionsException;
use function assert;

class ContactDetailType extends AbstractType
{

    protected const PATTERNS = [
        \OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_URL => "^(\+420|\+421)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$",
    ];

    protected const TYPES = [
        \OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_EMAIL => EmailType::class,
        \OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_PHONE => TelType::class,
        \OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_URL   => UrlType::class,
    ];

    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     *
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     * @throws OswisException
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $contactDetail = $builder->getData();
        if (!($contactDetail instanceof ContactDetail)) {
            throw new OswisException('Problém s vykreslením formuláře pro kontaktní údaj.');
        }
        $detailType = $contactDetail->getDetailType();
        $detailTypeString = $contactDetail->getDetailType() ? $contactDetail->getDetailType()->getType() : null;
        $options = [
            'label'       => $detailType ? $detailType->getFormLabel() : false,
            'required'    => $options['content_required'],
            'attr'        => [/*'autocomplete' => $contactDetail->getContactType() ? $contactDetail->getContactType()->getType() : true*/],
            'help'        => $detailType ? $detailType->getFormHelp() : null,
            'constraints' => self::getConstraintsByType($detailTypeString),
        ];
        $pattern = self::getPatternByType($detailTypeString);
        if ($pattern) {
            $options['attr']['pattern'] = $pattern;
        }
        $builder->add('content', self::getTypeByType($detailTypeString), $options);
    }

    /**
     * @param string|null $type
     *
     * @return array
     * @throws ConstraintDefinitionException
     * @throws InvalidOptionsException
     * @throws MissingOptionsException
     */
    public static function getConstraintsByType(?string $type = null): array
    {
        if (\OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_EMAIL === $type) {
            return [new Email(['mode' => 'strict', 'message' => 'Zadaná adresa {{ value }} není platná.'])];
        }
        if (\OswisOrg\OswisAddressBookBundle\Entity\ContactDetailType::TYPE_PHONE === $type) {
            return [
                new Regex(
                    [
                        'pattern' => "/^(\+420|\+421)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$/",
                        'message' => 'Zadané číslo {{ value }} není platným českým nebo slovenským telefonním číslem.',
                    ]
                ),
                new Length(['min' => 9, 'max' => 15]),
            ];
        }

        return [];
    }

    public static function getPatternByType(?string $type = null): ?string
    {
        return self::PATTERNS[$type] ?? null;
    }

    public static function getTypeByType(?string $type = null): string
    {
        return self::TYPES[$type] ?? TextType::class;
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class'       => ContactDetail::class,
                'content_required' => false,
                // 'attr' => ['class' => 'col-md-6'],
            )
        );
    }

    public function getName(): string
    {
        return 'address_book_contact_detail';
    }
}
