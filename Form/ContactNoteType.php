<?php
/**
 * @noinspection MethodShouldBeFinalInspection
 */

namespace OswisOrg\OswisAddressBookBundle\Form;

use OswisOrg\OswisAddressBookBundle\Entity\ContactNote;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Exception\AccessException;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function assert;

class ContactNoteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('content', TextType::class, [
            'label'    => 'Poznámka',
            'required' => $options['content_required'],
            'attr'     => ['placeholder' => false],
        ]);
        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $event) use ($options) {
            $contactNote = $event->getData();
            assert($contactNote instanceof ContactNote);
            $form = $event->getForm();
            $form->add('content', TextareaType::class, [
                'label'    => $contactNote->isPublic() ? 'Veřejná poznámka' : 'Interní poznámka',
                'required' => $options['content_required'],
                'help'     => $contactNote->isPublic() ? 'Poznámka, která může být zveřejněná.' : 'Neveřejná poznámka, určená pouze pro pořadatele.',
            ],);
        });
    }

    /**
     * @param  OptionsResolver  $resolver
     *
     * @throws AccessException
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class'       => ContactNote::class,
            'content_required' => false,
            //                'attr' => ['class' => 'col-md-6'],
        ]);
    }

    public function getName(): string
    {
        return 'address_book_contact_note';
    }
}
