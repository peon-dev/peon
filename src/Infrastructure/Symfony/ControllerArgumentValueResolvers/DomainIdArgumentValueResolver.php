<?php

declare(strict_types=1);

namespace Peon\Infrastructure\Symfony\ControllerArgumentValueResolvers;

use Peon\Domain\Job\Value\JobId;
use Peon\Domain\Project\Value\ProjectId;
use Peon\Domain\Task\Value\TaskId;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class DomainIdArgumentValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @var array<class-string>
     */
    private static array $supportedClasses = [
        ProjectId::class,
        JobId::class,
        TaskId::class,
    ];


    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return in_array($argument->getType(), self::$supportedClasses, true);
    }


    /**
     * @return \Generator<null|object>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        $attributeValue = $request->attributes->get($argument->getName());

        if ($attributeValue === null && $argument->isNullable()) {
            yield null;
        } else {
            assert(is_string($attributeValue));

            foreach (self::$supportedClasses as $class) {
                if ($class === $argument->getType()) {
                    yield new $class($attributeValue);
                }
            }
        }
    }
}
