<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectRecipesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('recipes', ChoiceType::class, [
           'choices' => [
               // TODO: we need dynamic choices here :-)
               'Unused private methods' => 'unused-private-methods',
               'Typed properties' => 'typed-properties',
           ],
            'expanded' => true,
            'multiple' => true,
        ]);
    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ProjectRecipesFormData::class,
        ]);
    }
}
