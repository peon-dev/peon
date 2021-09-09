<?php

declare(strict_types=1);

namespace PHPMate\Packages\PHPStan;

use Pepakriz\PHPStanExceptionRules\DynamicMethodThrowTypeExtension;
use Pepakriz\PHPStanExceptionRules\UnsupportedClassException;
use Pepakriz\PHPStanExceptionRules\UnsupportedFunctionException;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\ContextFactory;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ThrowsTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\ObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\UnionType;
use PHPStan\Type\VoidType;
use ReflectionClass;
use Symfony\Component\Messenger\MessageBusInterface;

class MessageBusExceptionExtension implements DynamicMethodThrowTypeExtension
{
    private Lexer $phpDocLexer;
    private PhpDocParser $phpDocParser;
    private ContextFactory $contextFactory;
    private TypeResolver $typeResolver;

    public function __construct(Lexer $phpDocLexer, PhpDocParser $phpDocParser)
    {
        $this->phpDocLexer = $phpDocLexer;
        $this->phpDocParser = $phpDocParser;
        $this->typeResolver = new TypeResolver();
        $this->contextFactory = new ContextFactory();
    }

    public function getThrowTypeFromMethodCall(MethodReflection $methodReflection, MethodCall $methodCall, Scope $scope): Type
    {
        if (!is_a($methodReflection->getDeclaringClass()->getName(), MessageBusInterface::class, true)) {
            throw new UnsupportedClassException();
        }

        if ($methodReflection->getName() !== 'dispatch') {
            throw new UnsupportedFunctionException();
        }

        /** @var ObjectType $valueType */
        $valueType = $scope->getType($methodCall->args[0]->value);
        $commandClassName = $valueType->getClassName();

        $handlerClassName = "{$commandClassName}Handler";
        if (!class_exists($handlerClassName)) {
            return new VoidType();
        }

        $classRef = new ReflectionClass($handlerClassName);
        $methodRef = $classRef->getMethod('__invoke');

        $phpDocString = $methodRef->getDocComment();
        if ($phpDocString === false) {
            return new VoidType();
        }

        $context = $this->contextFactory->createFromReflector($classRef);

        $tokens = new TokenIterator($this->phpDocLexer->tokenize($phpDocString));
        $phpDocNode = $this->phpDocParser->parse($tokens);

        $exceptions = [];

        foreach ($phpDocNode->children as $children) {
            if ($children instanceof PhpDocTagNode && $children->value instanceof ThrowsTagValueNode) {
                $node = $children->value->type;
                if ($node instanceof UnionTypeNode) {
                    foreach ($node->types as $type) {
                        $exceptions[] = new ObjectType((string) $this->typeResolver->resolve((string) $type, $context));
                    }
                } else {
                    $exceptions[] = new ObjectType((string) $this->typeResolver->resolve((string) $node, $context));
                }
            }
        }

        $count = count($exceptions);

        if ($count > 1) {
            return new UnionType($exceptions);
        }

        if ($count === 1) {
            return $exceptions[0];
        }

        return new VoidType();
    }
}
