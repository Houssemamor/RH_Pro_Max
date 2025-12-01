<?php

namespace App\Form;

use App\Entity\CandidateProfile;
use App\Entity\JobOffer;
use App\Entity\User;
use App\Enum\CandidateStatus;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CandidateProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('fullName')
            ->add('email')
            ->add('status', EnumType::class, [
                'class' => CandidateStatus::class,
            ])
            ->add('createdAt')
            ->add('jobOffer', EntityType::class, [
                'class' => JobOffer::class,
                'choice_label' => 'title',
            ])
            ->add('internalApplicant', EntityType::class, [
                'class' => User::class,
                'choice_label' => 'email',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CandidateProfile::class,
        ]);
    }
}
