<?php

declare(strict_types=1);

namespace Peon\Packages\PHPStan;

use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\ParametersAcceptorSelector;
use PHPStan\Type\ArrayType;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\IntegerType;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use Symplify\EasyHydrator\ArrayToValueObjectHydrator;

final class ArrayHydratorDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ArrayToValueObjectHydrator::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['hydrateArray', 'hydrateArrays'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        if (count($methodCall->args) <= 1) {
            return $this->getDefaultReturnType($methodReflection, $methodCall, $scope);
        }
        $arg = $methodCall->args[1]->value;
        if (!($arg instanceof ClassConstFetch)) {
            return $this->getDefaultReturnType($methodReflection, $methodCall, $scope);
        }
        $class = $arg->class;
        if (!($class instanceof Name)) {
            return $this->getDefaultReturnType($methodReflection, $methodCall, $scope);
        }
        $classFQN = $class->toString();
        if (!class_exists($classFQN)) {
            return $this->getDefaultReturnType($methodReflection, $methodCall, $scope);
        }
        if ($methodReflection->getName() === 'hydrateArray') {
            return new ObjectType($classFQN);
        }

        return new ArrayType(new IntegerType(), new ObjectType($classFQN));
    }

    private function getDefaultReturnType(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): Type {
        return ParametersAcceptorSelector::selectFromArgs(
            $scope,
            $methodCall->args,
            $methodReflection->getVariants()
        )->getReturnType();
    }
}
