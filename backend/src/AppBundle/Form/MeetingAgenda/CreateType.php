<?php

namespace AppBundle\Form\MeetingAgenda;

use AppBundle\Entity\MeetingAgenda;
use AppBundle\Entity\Meeting;
use AppBundle\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\NotBlank;

class CreateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array                $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('meeting', EntityType::class, [
                'class' => Meeting::class,
                'required' => false,
                'choice_label' => 'name',
                'placeholder' => 'placeholder.meeting',
                'translation_domain' => 'messages',
                'choice_translation_domain' => 'messages',
            ])
            ->add('topic', TextType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'not_blank.topic',
                    ]),
                ],
            ])
            ->add('responsibility', EntityType::class, [
                'class' => User::class,
                'required' => false,
                'choice_label' => 'username',
                'placeholder' => 'placeholder.user',
                'translation_domain' => 'messages',
            ])
            ->add('duration', IntegerType::class, [
                'required' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'not_blank.duration',
                    ]),
                    new GreaterThanOrEqual([
                        'value' => 0,
                        'message' => 'greater_than_or_equal.duration',
                    ]),
                ],
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MeetingAgenda::class,
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ]);
    }
}
