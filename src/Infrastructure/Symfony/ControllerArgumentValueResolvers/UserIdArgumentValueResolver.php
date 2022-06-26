<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Symfony\ControllerArgumentValueResolvers;

use Peon\Domain\User\Value\UserId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Http\Controller\UserValueResolver;

final class UserIdArgumentValueResolver implements ArgumentValueResolverInterface
{
    public function __construct(
        private readonly Security $security,
    ) {}


    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === UserId::class;
    }


    /**
     * @see UserValueResolver
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $user = $this->security->getUser();

        if ($user === null) {
            if (!$argument->isNullable()) {
                throw new AccessDeniedException(sprintf('There is no logged-in user to pass to $%s, make the argument nullable if you want to allow anonymous access to the action.', $argument->getName()));
            }

            yield null;
        } else {
            yield new UserId($user->getUserIdentifier());
        }
    }
}
