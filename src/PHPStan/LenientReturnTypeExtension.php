<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PhpParser\Node\Expr\StaticCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicStaticMethodReturnTypeExtension;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeWithClassName;
use Sourcetoad\ShapeParser\Modifiers;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\LenientListParser;
use Sourcetoad\ShapeParser\Parsers\LenientParser;
use Sourcetoad\ShapeParser\Parsers\LenientRecordParser;
use Sourcetoad\ShapeParser\Parsers\ListParser;
use Sourcetoad\ShapeParser\Parsers\RecordParser;

class LenientReturnTypeExtension implements DynamicStaticMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return Modifiers::class;
    }

    public function isStaticMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'lenient';
    }

    public function getTypeFromStaticMethodCall(
        MethodReflection $methodReflection,
        StaticCall $methodCall,
        Scope $scope,
    ): ?Type {
        $args = $methodCall->getArgs();

        if (count($args) < 1) {
            return null;
        }

        $argType = $scope->getType($args[0]->value);

        // ListParser -> LenientListParser<T>
        if ($this->isSubtypeOf($argType, ListParser::class)) {
            $genericType = $this->resolveGenericParam($argType, ParserContract::class);

            if ($genericType === null) {
                return null;
            }

            // ListParser<T> extends BaseParser<list<T>>, so the generic is list<T>.
            // We need to extract T from list<T>.
            $iterableValueType = $genericType->getIterableValueType();

            return new GenericObjectType(LenientListParser::class, [$iterableValueType]);
        }

        // RecordParser -> LenientRecordParser<K, T>
        if ($this->isSubtypeOf($argType, RecordParser::class)) {
            $genericType = $this->resolveGenericParam($argType, ParserContract::class);

            if ($genericType === null) {
                return null;
            }

            // RecordParser<K, T> extends BaseParser<array<K, T>>, so the generic is array<K, T>.
            $iterableKeyType = $genericType->getIterableKeyType();
            $iterableValueType = $genericType->getIterableValueType();

            return new GenericObjectType(LenientRecordParser::class, [$iterableKeyType, $iterableValueType]);
        }

        // All others -> LenientParser<T>
        $genericType = $this->resolveGenericParam($argType, ParserContract::class);

        if ($genericType === null) {
            return null;
        }

        return new GenericObjectType(LenientParser::class, [$genericType]);
    }

    private function isSubtypeOf(Type $type, string $className): bool
    {
        if (! method_exists($type, 'getAncestorWithClassName')) {
            return false;
        }

        return $type->getAncestorWithClassName($className) !== null;
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
