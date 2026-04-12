<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;
use Sourcetoad\ShapeParser\ShapeFactory;

class ShapeFactoryDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ShapeFactory::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array($methodReflection->getName(), ['discriminatedUnion', 'literal', 'object'], true);
    }

    public function getTypeFromMethodCall(
        MethodReflection $methodReflection,
        MethodCall $methodCall,
        Scope $scope
    ): ?Type {
        $methodArguments = $methodCall->getArgs();

        if (count($methodArguments) === 0) {
            return null;
        }

        return match ($methodReflection->getName()) {
            'discriminatedUnion' => $this->resolveDiscriminatedUnion($methodCall, $scope),
            'literal' => new GenericObjectType(LiteralParser::class, [
                $scope->getType($methodArguments[0]->value),
            ]),
            'object' => (new ShapeTypeResolver)->resolve(
                $scope->getType($methodArguments[0]->value),
            ),
            default => null,
        };
    }

    private function resolveDiscriminatedUnion(MethodCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) < 2) {
            return null;
        }

        $parsersType = $scope->getType($args[1]->value);
        $constantArrays = $parsersType->getConstantArrays();

        if (count($constantArrays) === 0) {
            return null;
        }

        $variantTypes = [];

        foreach ($constantArrays as $constantArray) {
            foreach ($constantArray->getValueTypes() as $valueType) {
                if (!method_exists($valueType, 'getAncestorWithClassName')) {
                    continue;
                }

                /** @var TypeWithClassName|null $ancestor */
                // @phpstan-ignore phpstanApi.varTagAssumption
                $ancestor = $valueType->getAncestorWithClassName(ObjectParser::class);

                if ($ancestor === null || !method_exists($ancestor, 'getTypes')) {
                    continue;
                }

                /** @var array<int, Type> $typeParams */
                $typeParams = $ancestor->getTypes();

                if (count($typeParams) > 0) {
                    $variantTypes[] = $typeParams[0];
                }
            }
        }

        if (count($variantTypes) === 0) {
            return null;
        }

        return new GenericObjectType(DiscriminatedUnionParser::class, [
            TypeCombinator::union(...$variantTypes),
        ]);
    }
}
