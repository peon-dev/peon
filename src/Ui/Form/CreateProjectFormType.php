<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class CreateProjectFormType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('remoteRepositoryUri', TextType::class, [
            'label' => 'Git repository',
            'attr' => [
                'placeholder' => 'https://github.com/peon-dev/peon.git',
            ],
            'help_html' => true,
            'help' => 'Supported git providers: <strong>GitHub</strong>, <strong>GitLab</strong> (both cloud and self-hosted)',
        ]);

        $builder->add('personalAccessToken', TextType::class, [
            'label' => 'Personal Access Token',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateProjectFormData::class,
        ]);
    }
}
