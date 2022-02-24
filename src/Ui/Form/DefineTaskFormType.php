<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DefineTaskFormType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('name', TextType::class, [
            'label' => 'Task name',
        ]);

        $builder->add('schedule', TextType::class, [
            'label' => 'Schedule',
            'required' => false,
            'help_html' => true,
            'help' => 'For example <code>0 * * * *</code> to run every hour<br>More about <a href="https://en.wikipedia.org/wiki/Cron" target="_blank">CRON expression <i class="fas fa-external-link-alt"></i></a><br>Leave empty for manual triggering only',
        ]);

        $builder->add('mergeAutomatically', CheckboxType::class, [
            'label' => 'Merge automatically if CI passes',
            'required' => false,
            'help_html' => true,
            'help' => 'Auto-merge must be enabled in the repository settings.',
        ]);

        $builder->add('commands', TextareaType::class, [
            'label' => 'Commands',
            'help_html' => true,
            'help' => 'For example <code>vendor/bin/rector process src</code><br>One command per line',
            'attr' => ['class' => 'code'],
        ]);

        $builder->add('save', SubmitType::class, [
            'label' => 'Save',
            'attr' => [
                'value' => '',
            ],
            'row_attr' => [
                'class' => 'multiple-form-buttons',
            ],
        ]);

        $builder->add('saveAndRun', SubmitType::class, [
            'label' => 'Save & Run',
            'attr' => [
                'class' => 'btn-outline-primary',
                'value' => '',
            ],
            'row_attr' => [
                'class' => 'multiple-form-buttons',
            ],
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => DefineTaskFormData::class,
        ]);
    }
}
