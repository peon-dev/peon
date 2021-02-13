<?php
declare (strict_types=1);

namespace Acme\Domain\Gitlab;

interface OpenGitlabMergeRequest
{
    public function __invoke(GitlabApplication $gitlabApplication): void;
}
