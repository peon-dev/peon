<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Symfony\ControllerArgumentValueResolvers;

use Peon\Domain\Project\Value\ProjectId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ProjectIdArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === ProjectId::class;
    }

    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {

        yield new ProjectId(
            $request->attributes->get($argument->getName()),
        );
    }
}
