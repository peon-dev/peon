<?php

declare(strict_types=1);

namespace PHPMate\Infrastructure\Gitlab;

use Gitlab\Client;
use PHPMate\Domain\Gitlab\Gitlab;
use PHPMate\Domain\Gitlab\GitlabRepository;

final class HttpGitlabClient implements Gitlab
{
    public function openMergeRequest(GitlabRepository $gitlabRepository, string $targetBranch, string $branchWithChanges): void
    {
        $client = $this->createClient($gitlabRepository);
        $project = $gitlabRepository->getProject();

        $client->mergeRequests()->create(
            $project,
            $branchWithChanges,
            $targetBranch,
            'PHPMate changes' // TODO dynamic
        );
    }


    private function createClient(GitlabRepository $repository): Client
    {
        $uri = $repository->getAuthenticatedRepositoryUri();
        $domain = $uri->getScheme() . '://' . $uri->getHost();
        $personalAccessToken = $repository->authentication->personalAccessToken;

        $client = new Client();
        $client->setUrl($domain);
        $client->authenticate($personalAccessToken, Client::AUTH_HTTP_TOKEN);

        return $client;
    }
}
