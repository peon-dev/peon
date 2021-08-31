<?php

declare(strict_types=1);

namespace PHPMate\Ui\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\NotBlank;

final class CreateProjectFormType extends AbstractType
{
    /**
     * @param mixed[] $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('remoteRepositoryUri', TextType::class, [
            'label' => 'Gitlab git repository:',
            'attr' => [
                'placeholder' => 'https://gitlab.com/phpmate/phpmate.git',
            ],
        ]);

        $builder->add('personalAccessToken', TextType::class, [
            'label' => 'Personal Access Token:',
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CreateProjectFormData::class,
        ]);
    }
}
