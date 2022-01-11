<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Symfony\Component\Validator\Constraints\NotBlank;

final class CreateProjectFormData
{
    #[NotBlank]
    public string $remoteRepositoryUri;

    #[NotBlank]
    public string $personalAccessToken;
}
