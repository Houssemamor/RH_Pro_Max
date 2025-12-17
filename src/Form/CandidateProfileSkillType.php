<?php

namespace App\Form;

use App\Entity\CandidateProfileSkill;
use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CandidateProfileSkillType Form
 * 
 * Purpose: Form for candidates to add skills to their profile.
 * Includes years of experience field for better skill context.
 */
class CandidateProfileSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
                'placeholder' => 'Select a skill',
                'attr' => ['class' => 'form-control'],
                'label' => 'Skill'
            ])
            ->add('level', EnumType::class, [
                'class' => SkillLevel::class,
                'choice_label' => fn(SkillLevel $level) => ucfirst(strtolower($level->name)),
                'attr' => ['class' => 'form-control'],
                'label' => 'Your Level'
            ])
            ->add('yearsExperience', IntegerType::class, [
                'required' => false,
                'attr' => ['class' => 'form-control', 'min' => 0],
                'label' => 'Years of Experience'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CandidateProfileSkill::class,
        ]);
    }
}
