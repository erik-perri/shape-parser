<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PHPStan\Type\Constant\ConstantArrayTypeBuilder;
use PHPStan\Type\Generic\GenericObjectType;
use PHPStan\Type\Type;
use PHPStan\Type\TypeCombinator;
use PHPStan\Type\TypeWithClassName;
use Sourcetoad\ShapeParser\ParserContract;
use Sourcetoad\ShapeParser\Parsers\ObjectParser;

class ShapeTypeResolver
{
    public function resolve(Type $shapeType): ?Type
    {
        $constantArrays = $shapeType->getConstantArrays();

        if (count($constantArrays) === 0) {
            return null;
        }

        $results = [];

        foreach ($constantArrays as $inputArrayShape) {
            $builder = ConstantArrayTypeBuilder::createEmpty();
            $valueTypes = $inputArrayShape->getValueTypes();

            foreach ($inputArrayShape->getKeyTypes() as $idx => $keyType) {
                $parserType = $valueTypes[$idx];

                $parserResultType = $this->resolveParserContractGeneric($parserType);

                if (!$parserResultType) {
                    continue;
                }

                $builder->setOffsetValueType($keyType, $parserResultType);
            }

            $results[] = new GenericObjectType(ObjectParser::class, [$builder->getArray()]);
        }

        return TypeCombinator::union(...$results);
    }

    private function resolveParserContractGeneric(Type $parserType): ?Type
    {
        if (!method_exists($parserType, 'getAncestorWithClassName')) {
            return null;
        }

        /** @var TypeWithClassName|null $ancestor */
        // @phpstan-ignore phpstanApi.varTagAssumption
        $ancestor = $parserType->getAncestorWithClassName(ParserContract::class);

        if ($ancestor === null) {
            return null;
        }

        if (!method_exists($ancestor, 'getTypes')) {
            return null;
        }

        /** @var array<int, Type> $genericTypes */
        $genericTypes = $ancestor->getTypes();

        return empty($genericTypes)
            ? null
            : $genericTypes[0];
    }
}
