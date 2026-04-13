<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\DiscriminatedUnionParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\LiteralParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;
use Sourcetoad\ShapeParser\Parsers\UnionParser;
use Sourcetoad\ShapeParser\Shape;

class ShapeDynamicReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Shape::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return in_array(
            $methodReflection->getName(),
            ['discriminatedUnion', 'list', 'literal', 'object', 'record', 'union'],
            true,
        );
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): ?Type {
        $methodArguments = $methodCall->getArgs();

        if (count($methodArguments) === 0) {
            return null;
        }

        return match ($methodReflection->getName()) {
            'discriminatedUnion' => $this->resolveDiscriminatedUnion($methodCall, $scope),
            'list' => $this->resolveList($methodCall, $scope),
            'literal' => new GenericObjectType(LiteralParser::class, [
                $scope->getType($methodArguments[0]->value),
            ]),
            'object' => (new ShapeTypeResolver)->resolve(
                $scope->getType($methodArguments[0]->value),
            ),
            'record' => $this->resolveRecord($methodCall, $scope),
            'union' => $this->resolveUnion($methodCall, $scope),
            default => null,
        };
    }

    private function resolveDiscriminatedUnion(StaticCall $methodCall, Scope $scope): ?Type
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
                $variantType = $this->resolveParserContractGeneric($valueType);

                if ($variantType !== null) {
                    $variantTypes[] = $variantType;
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

    private function resolveList(StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) < 1) {
            return null;
        }

        $innerType = $this->resolveParserContractGeneric($scope->getType($args[0]->value));

        if ($innerType === null) {
            return null;
        }

        return new GenericObjectType(ListParser::class, [$innerType]);
    }

    private function resolveUnion(StaticCall $methodCall, Scope $scope): ?Type
    {
        $variantTypes = [];

        foreach ($methodCall->getArgs() as $arg) {
            $variantType = $this->resolveParserContractGeneric($scope->getType($arg->value));

            if ($variantType === null) {
                return null;
            }

            $variantTypes[] = $variantType;
        }

        if (count($variantTypes) === 0) {
            return null;
        }

        return new GenericObjectType(UnionParser::class, [
            TypeCombinator::union(...$variantTypes),
        ]);
    }

    private function resolveRecord(StaticCall $methodCall, Scope $scope): ?Type
    {
        $args = $methodCall->getArgs();

        if (count($args) < 2) {
            return null;
        }

        $keyType = $this->resolveParserContractGeneric($scope->getType($args[0]->value));
        $valueType = $this->resolveParserContractGeneric($scope->getType($args[1]->value));

        if ($keyType === null || $valueType === null) {
            return null;
        }

        return new GenericObjectType(RecordParser::class, [$keyType, $valueType]);
    }

    private function resolveParserContractGeneric(Type $parserType): ?Type
    {
        if (! method_exists($parserType, 'getAncestorWithClassName')) {
            return null;
        }

        /** @var TypeWithClassName|null $ancestor */
        // @phpstan-ignore phpstanApi.varTagAssumption
        $ancestor = $parserType->getAncestorWithClassName(ParserContract::class);

        if ($ancestor === null || ! method_exists($ancestor, 'getTypes')) {
            return null;
        }

        /** @var array<int, Type> $genericTypes */
        $genericTypes = $ancestor->getTypes();

        return empty($genericTypes) ? null : $genericTypes[0];
    }
}
