<?php

namespace App\Form;

use App\Entity\CandidateProfile;
use App\Entity\CandidateSkill;
use App\Entity\Skill;
use App\Enum\SkillLevel;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('level', EnumType::class, [
                'class' => SkillLevel::class,
            ])
            ->add('confidence')
            ->add('candidateProfile', EntityType::class, [
                'class' => CandidateProfile::class,
                'choice_label' => 'fullName',
            ])
            ->add('skill', EntityType::class, [
                'class' => Skill::class,
                'choice_label' => 'name',
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
