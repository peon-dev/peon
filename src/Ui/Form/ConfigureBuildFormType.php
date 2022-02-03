<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ConfigureBuildFormType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('skipComposerInstall', CheckboxType::class, [
            'label' => 'Skip composer installation',
            'required' => false,
            'help_html' => true,
            'help' => 'Useful when vendor directory is versioned (directly part of the git repository)',
        ]);

        // Workaround when all checkboxes are false values
        $builder->add('workaround', HiddenType::class, [
            'mapped' => false,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => ConfigureBuildFormData::class,
        ]);
    }
}
