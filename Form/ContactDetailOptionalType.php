<?php

namespace Zakjakub\OswisAddressBookBundle\Form;

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
use Zakjakub\OswisAddressBookBundle\Entity\ContactDetail;
use function assert;

class ContactDetailOptionalType extends AbstractType
{
    final public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add(
            'content',
            TextType::class,
            array(
                'label'    => false,
                'required' => false,
                'attr'     => ['placeholder' => 'Kontakt'],
            )
        );
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            static function (FormEvent $event) {
                $contactDetail = $event->getData();
                assert($contactDetail instanceof ContactDetail);
                $form = $event->getForm();
                $type = TextType::class;
                $constraints = [];
                $pattern = null;
                if ($contactDetail->getContactType()) {
                    switch ($contactDetail->getContactType()->getType()) {
                        case 'email':
                            $type = EmailType::class;
                            $constraints = [
                                new Email(['mode' => 'strict', 'message' => 'Zadaná adresa {{ value }} není platná.']),
                            ];
                            break;
                        case 'url':
                            $type = UrlType::class;
                            break;
                        case 'phone':
                            $type = TelType::class;
                            $pattern = "^(\+420|\+421)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$";
                            $constraints = [
                                new Regex(
                                    [
                                        'pattern' => "/^(\+420|\+421)? ?[1-9][0-9]{2} ?[0-9]{3} ?[0-9]{3}$/",
                                        'message' => 'Zadané číslo {{ value }} není platným českým nebo slovenským telefonním číslem.',
                                    ]
                                ),
                                new Length(['min' => 9, 'max' => 18,]),
                            ];
                            break;
                    }
                }
                $options = array(
                    'label'       => $contactDetail->getContactType() ? $contactDetail->getContactType()->getFormLabel() : false,
                    'required'    => false,
                    'attr'        => [// 'autocomplete' => $contactDetail->getContactType() ? $contactDetail->getContactType()->getType() : true,
                    ],
                    'help'        => $contactDetail->getContactType() ? $contactDetail->getContactType()->getFormHelp() : null,
                    'constraints' => $constraints,
                );
                if ($pattern) {
                    $options['attr']['pattern'] = $pattern;
                }
                $form->add('content', $type, $options);
            }
        );
    }

    /**
     * @param OptionsResolver $resolver
     *
     * @throws AccessException
     */
    final public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            array(
                'data_class' => ContactDetail::class,
//                'attr' => ['class' => 'col-md-6'],
            )
        );
    }

    final public function getName(): string
    {
        return 'address_book_contact_detail_optional';
    }
}
