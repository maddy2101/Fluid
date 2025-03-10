<?php

declare(strict_types=1);

/*
 * This file belongs to the package "TYPO3 Fluid".
 * See LICENSE.txt that was shipped with this package.
 */

namespace TYPO3Fluid\Fluid\Tests\Unit\Core\Variables;

use TYPO3Fluid\Fluid\Core\Variables\StandardVariableProvider;
use TYPO3Fluid\Fluid\Core\Variables\VariableExtractor;
use TYPO3Fluid\Fluid\Tests\Functional\Fixtures\Various\UserWithoutToString;
use TYPO3Fluid\Fluid\Tests\Unit\Core\Fixtures\ClassWithMagicGetter;
use TYPO3Fluid\Fluid\Tests\UnitTestCase;

/**
 * Class VariableExtractorTest
 */
class VariableExtractorTest extends UnitTestCase
{

    /**
     * @param mixed $subject
     * @param string $path
     * @param mixed $expected
     * @test
     * @dataProvider getPathTestValues
     */
    public function testGetByPath($subject, $path, $expected)
    {
        $result = VariableExtractor::extract($subject, $path);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getPathTestValues()
    {
        $namedUser = new UserWithoutToString('Foobar Name');
        $unnamedUser = new UserWithoutToString('');
        return [
            [null, '', null],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], 'foo.invalid', null],
            [['user' => $namedUser], 'user.name', 'Foobar Name'],
            [['user' => $unnamedUser], 'user.name', ''],
            [['user' => $namedUser], 'user.named', true],
            [['user' => $unnamedUser], 'user.named', false],
            [['user' => $namedUser], 'user.invalid', null],
            [['foodynamicbar' => 'test', 'dyn' => 'dynamic'], 'foo{dyn}bar', 'test'],
            [['foo' => ['dynamic' => ['bar' => 'test']], 'dyn' => 'dynamic'], 'foo.{dyn}.bar', 'test'],
            [['foo' => ['bar' => 'test'], 'dynamic' => ['sub' => 'bar'], 'baz' => 'sub'], 'foo.{dynamic.{baz}}', 'test'],
            [['user' => $namedUser], 'user.hasAccessor', true],
            [['user' => $namedUser], 'user.isAccessor', true],
            [['user' => $unnamedUser], 'user.hasAccessor', false],
            [['user' => $unnamedUser], 'user.isAccessor', false],
        ];
    }

    /**
     * @param mixed $subject
     * @param string $path
     * @param mixed $expected
     * @test
     * @dataProvider getAccessorsForPathTestValues
     */
    public function testGetAccessorsForPath($subject, $path, $expected)
    {
        $result = VariableExtractor::extractAccessors($subject, $path);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getAccessorsForPathTestValues()
    {
        $namedUser = new UserWithoutToString('Foobar Name');
        $inArray = ['user' => $namedUser];
        $inArrayAccess = new StandardVariableProvider($inArray);
        $inPublic = (object)$inArray;
        $asArray = VariableExtractor::ACCESSOR_ARRAY;
        $asGetter = VariableExtractor::ACCESSOR_GETTER;
        $asPublic = VariableExtractor::ACCESSOR_PUBLICPROPERTY;
        return [
            [null, '', []],
            [['inArray' => $inArray], 'inArray.user', [$asArray, $asArray]],
            [['inArray' => $inArray], 'inArray.user.name', [$asArray, $asArray, $asGetter]],
            [['inArrayAccess' => $inArrayAccess], 'inArrayAccess.user.name', [$asArray, $asArray, $asGetter]],
            [['inArrayAccessWithGetter' => $inArrayAccess], 'inArrayAccessWithGetter.allIdentifiers', [$asArray, $asGetter]],
            [['inPublic' => $inPublic], 'inPublic.user.name', [$asArray, $asPublic, $asGetter]],
        ];
    }

    /**
     * @param mixed $subject
     * @param string $path
     * @param string $accessor
     * @param mixed $expected
     * @test
     * @dataProvider getExtractRedectAccessorTestValues
     */
    public function testExtractRedetectsAccessorIfUnusableAccessorPassed($subject, $path, $accessor, $expected)
    {
        $result = VariableExtractor::extract($subject, $path, [$accessor]);
        self::assertEquals($expected, $result);
    }

    /**
     * @return array
     */
    public function getExtractRedectAccessorTestValues()
    {
        return [
            [['test' => 'test'], 'test', null, 'test'],
            [['test' => 'test'], 'test', 'garbageextractionname', 'test'],
            [['test' => 'test'], 'test', VariableExtractor::ACCESSOR_PUBLICPROPERTY, 'test'],
            [['test' => 'test'], 'test', VariableExtractor::ACCESSOR_GETTER, 'test'],
            [['test' => 'test'], 'test', VariableExtractor::ACCESSOR_ASSERTER, 'test'],
            [(object)['test' => 'test'], 'test', VariableExtractor::ACCESSOR_ARRAY, 'test'],
            [(object)['test' => 'test'], 'test', VariableExtractor::ACCESSOR_ARRAY, 'test'],
            [new \ArrayObject(['testProperty' => 'testValue']), 'testProperty', null, 'testValue'],
        ];
    }

    /**
     * @test
     */
    public function testExtractCallsMagicMethodGetters()
    {
        $subject = new ClassWithMagicGetter();
        $result = VariableExtractor::extract($subject, 'test');
        self::assertEquals('test result', $result);
    }
}
