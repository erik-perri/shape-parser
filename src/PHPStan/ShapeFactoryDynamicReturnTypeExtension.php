<?php

declare(strict_types=1);

namespace Sourcetoad\ShapeParser\PHPStan;

use PhpParser\Node\Expr\MethodCall;
use PHPStan\Analyser\Scope;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Type\DynamicMethodReturnTypeExtension;
use PHPStan\Type\Type;
use Sourcetoad\ShapeParser\ShapeFactory;

class ShapeFactoryDynamicReturnTypeExtension implements DynamicMethodReturnTypeExtension
{
    public function getClass(): string
    {
        return ShapeFactory::class;
    }

    public function isMethodSupported(MethodReflection $methodReflection): bool
    {
        return $methodReflection->getName() === 'object';
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

        $shapeType = $scope->getType($methodArguments[0]->value);

        return (new ShapeTypeResolver)->resolve($shapeType);
    }
}
