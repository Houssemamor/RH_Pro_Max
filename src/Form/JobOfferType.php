<?php

namespace App\Form;

use App\Entity\JobOffer;
use App\Entity\User;
use App\Enum\JobOfferStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * JobOfferType Form
 * 
 * Form for creating and editing job offers.
 * Includes skill requirements through embedded collection.
 */
class JobOfferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title')
            ->add('description')
            ->add('location')
            ->add('status', EnumType::class, [
                'class' => JobOfferStatus::class,
            ])
            ->add('createdAt')
            ->add('closingDate')
            ->add('creator', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
            ->add('skills', CollectionType::class, [
                'entry_type' => JobOfferSkillType::class,
                'entry_options' => ['label' => false],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'Required Skills',
                'attr' => [
                    'class' => 'skills-collection'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOffer::class,
        ]);
    }
}
