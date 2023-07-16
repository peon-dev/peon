<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfigureRecipeFormType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('mergeAutomatically', CheckboxType::class, [
            'label' => 'Merge automatically if CI passes',
            'required' => false,
            'help_html' => true,
            'help' => 'Auto-merge must be enabled in the repository settings.<br>This option has no effect if auto-merge is not configured properly in your git provider settings.',
        ]);

        $builder->add('afterScript', TextType::class, [
            'label' => 'After script',
            'required' => false,
            'help_html' => true,
            'empty_data' => '',
            'help' => 'Command that will be run after recipe is finished.',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigureRecipeFormData::class,
        ]);
    }
}
