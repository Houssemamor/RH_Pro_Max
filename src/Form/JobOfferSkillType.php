<?php

namespace App\Form;

use App\Entity\JobOfferSkill;
use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * JobOfferSkillType Form
 * 
 * Purpose: Form for adding/editing skills required for a job offer.
 * Allows selection of skill, required level, and whether it's mandatory.
 * 
 * Design Pattern: Form Builder Pattern (Symfony)
 */
class JobOfferSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a skill',
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Skill'
            ])
            ->add('requiredLevel', EnumType::class, [
                'class' => SkillLevel::class,
                'choice_label' => fn(SkillLevel $level) => ucfirst(strtolower($level->name)),
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Required Level'
            ])
            ->add('isRequired', CheckboxType::class, [
                'required' => false,
                'label' => 'Mandatory Skill',
                'attr' => [
                    'class' => 'form-check-input'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => JobOfferSkill::class,
        ]);
    }
}
