<?php

declare(strict_types=1);

namespace Peon\Ui\Form;

use Peon\Ui\ReadModel\ProjectDetail\ReadProjectDetail;

final class ConfigureBuildFormData
{
    public bool $skipComposerInstall;


    public static function fromReadProjectDetail(ReadProjectDetail $project): self
    {
        $data = new self();
        $data->skipComposerInstall = $project->skipComposerInstall;

        return $data;
    }
}
