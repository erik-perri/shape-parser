<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\BaseParser;
use Sourcetoad\ShapeParser\Parsers\NullableParser;

class NullableReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return BaseParser::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'nullable';
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $callerType = $scope->getType($methodCall->var);

        $innerType = $this->resolveGenericParam($callerType, ParserContract::class);

        if ($innerType === null) {
            return null;
        }

        return new GenericObjectType(NullableParser::class, [$innerType]);
    }

    private function resolveGenericParam(Type $type, string $ancestorClass): ?Type
    {
        if (! method_exists($type, 'getAncestorWithClassName')) {
            return null;
        }

        /** @var TypeWithClassName|null $ancestor */
        // @phpstan-ignore phpstanApi.varTagAssumption
        $ancestor = $type->getAncestorWithClassName($ancestorClass);

        if ($ancestor === null || ! method_exists($ancestor, 'getTypes')) {
            return null;
        }

        /** @var array<int, Type> $genericTypes */
        $genericTypes = $ancestor->getTypes();

        return empty($genericTypes) ? null : $genericTypes[0];
    }
}
