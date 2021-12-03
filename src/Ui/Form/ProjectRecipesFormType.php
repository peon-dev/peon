<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

use PHPMate\Infrastructure\Cookbook\StaticRecipesCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ProjectRecipesFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // TODO: get rid of this ugly static, temporary hack
        $recipesCollection = new StaticRecipesCollection();

        $choices = [];
        foreach ($recipesCollection->all() as $recipe) {
            $choices[$recipe->title] = $recipe->name->toString();
        }

        $builder->add('recipes', ChoiceType::class, [
           'choices' => $choices,
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
