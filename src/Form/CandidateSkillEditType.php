<?php

namespace App\Form;

use App\Entity\CandidateSkill;
use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * CandidateSkillEditType Form
 * 
 * Purpose: Form for candidates to add/edit their skills.
 * Simpler than CandidateSkillType - excludes confidence and candidateProfile fields.
 */
class CandidateSkillEditType extends AbstractType
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
            ->add('level', EnumType::class, [
                'class' => SkillLevel::class,
                'choice_label' => fn(SkillLevel $level) => ucfirst(strtolower($level->name)),
                'attr' => [
                    'class' => 'form-control'
                ],
                'label' => 'Your Level'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CandidateSkill::class,
        ]);
    }
}
