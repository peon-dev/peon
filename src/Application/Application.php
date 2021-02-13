<?php
declare (strict_types=1);

namespace Acme\Application;

class Application
{
    private string $gitlabRepository;


    public function __construct(string $gitlabRepository)
    {
        $this->gitlabRepository = $gitlabRepository;
    }
}
