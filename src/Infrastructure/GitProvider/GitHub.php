<?php

declare(strict_types=1);

namespace Peon\Infrastructure\GitProvider;

use Github\AuthMethod;
use Github\Client;
use Github\HttpClient\Builder;
use Peon\Domain\GitProvider\Exception\AutoMergeNotSupported;
use Peon\Domain\GitProvider\Exception\GitProviderCommunicationFailed;
use Peon\Domain\GitProvider\GitProvider;
use Peon\Domain\GitProvider\Value\Commit;
use Peon\Domain\GitProvider\Value\MergeRequest;
use Peon\Domain\GitProvider\Value\RemoteGitRepository;

final class GitHub implements GitProvider
{
    public function __construct(
        private readonly Builder $clientBuilder,
    ) {}


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function openMergeRequest(RemoteGitRepository $gitRepository, string $targetBranch, string $branchWithChanges, string $title, string $description): MergeRequest
    {
        $repositoryOwner = $gitRepository->getProjectUsername();
        $client = $this->createClient($gitRepository);

        $pullRequest = $client->pullRequests()->create(
            $repositoryOwner,
            $gitRepository->getProjectRepository(),
            [
                'title' => $title,
                'head' => $repositoryOwner . ':' . $branchWithChanges,
                'base' => $targetBranch,
                'body' => $description,
            ]
        );

        // TODO: labels etc
        // Notes from GH: Every pull request is an issue, but not every issue is a pull request. For this reason, "shared" actions for both features, like manipulating assignees, labels and milestones, are provided within the Issues API.

        return new MergeRequest(
            (string) $pullRequest['id'],
            $pullRequest['html_url'],
        );
    }


    /**
     * @throws GitProviderCommunicationFailed
     */
    public function getMergeRequestForBranch(RemoteGitRepository $gitRepository, string $branch): MergeRequest|null
    {
        $repositoryOwner = $gitRepository->getProjectUsername();
        $client = $this->createClient($gitRepository);

        $pullRequests = $client->pullRequests()->all(
            $repositoryOwner,
            $gitRepository->getProjectRepository(),
            [
                'state' => 'open',
                'head' => $repositoryOwner . ':' . $branch,
            ]
        );

        if ($pullRequests === []) {
            return null;
        }

        if (count($pullRequests) > 1) {
            throw new GitProviderCommunicationFailed('Should not exist more than 1 merge request for branch');
        }

        return new MergeRequest(
            (string) $pullRequests[0]['id'],
            $pullRequests[0]['html_url'],
        );
    }


    public function isAutoMergeSupported(): bool
    {
        return false;
    }


    public function mergeAutomatically(RemoteGitRepository $gitRepository, MergeRequest $mergeRequest): void
    {
        throw new AutoMergeNotSupported();
    }


    public function getLastCommitOfDefaultBranch(RemoteGitRepository $gitRepository): Commit
    {
        $client = $this->createClient($gitRepository);

        /*
        $repository = $client->repository()->show(

        );
        */

        return new Commit('');
    }


    public function hasWriteAccessToRepository(RemoteGitRepository $gitRepository): bool
    {
        $client = $this->createClient($gitRepository);

        $repository = $client->repository()->show(
            $gitRepository->getProjectUsername(),
            $gitRepository->getProjectRepository(),
        );

        $allowedPermissions = ['admin', 'maintain', 'push'];

        foreach ($allowedPermissions as $permission) {
            if ($repository['permissions'][$permission] === true) {
                return true;
            }
        }

        return false;
    }


    public function createClient(RemoteGitRepository $gitRepository): Client
    {
        $client = new Client($this->clientBuilder);
        $client->authenticate(
            $gitRepository->authentication->password,
            authMethod: AuthMethod::ACCESS_TOKEN,
        );

        return $client;
    }
}
