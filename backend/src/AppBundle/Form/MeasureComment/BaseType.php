<?php

namespace AppBundle\Form\MeasureComment;

use AppBundle\Entity\MeasureComment;
use AppBundle\Entity\Measure;
use AppBundle\Entity\User;
use AppBundle\Form\WorkPackage\UploadMediaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class BaseType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextareaType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'not_blank.description',
                    ]),
                ],
            ])
            ->add('responsibility', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'username',
                'placeholder' => 'placeholder.user',
                'translation_domain' => 'messages',
            ])
            ->add('measure', EntityType::class, [
                'class' => Measure::class,
                'choice_label' => 'title',
                'placeholder' => 'placeholder.title',
                'translation_domain' => 'messages',
            ])
            ->add('medias', CollectionType::class, [
                'entry_type' => UploadMediaType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MeasureComment::class,
            'allow_extra_fields' => true,
        ]);
    }
}
