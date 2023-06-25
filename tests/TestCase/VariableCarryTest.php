<?php

/*
 * Copyright (c) 2023 nojimage
 */

declare(strict_types=1);

namespace Nojimage\PHPvJS\Test\TestCase;

use Nojimage\PHPvJS\VariableCarry;
use PHPUnit\Framework\TestCase;

class VariableCarryTest extends TestCase
{
    /**
     * @var VariableCarry
     */
    private $carray;

    protected function setUp(): void
    {
        parent::setUp();
        $this->carray = new VariableCarry();
    }

    /**
     * attribute name is class name
     *
     * @return void
     */
    public function testGetAttributeName(): void
    {
        $this->assertSame('Nojimage\PHPvJS\VariableCarry', $this->carray->getAttributeName());
    }

    /**
     * Can set variable to JavaScript
     *
     * @dataProvider dataRenderScriptTag
     * @return void
     */
    public function testRenderScriptTag(array $data, string $expects): void
    {
        $this->carray->setJsData($data);
        $this->assertSame($expects, $this->carray->renderScriptTag());
    }

    /**
     * @return array[]
     */
    public function dataRenderScriptTag(): array
    {
        return [
            'variables empty will return empty string' => [
                [],
                '',
            ],
            'set with scalar values' => [
                [
                    'strVar' => 'string value',
                    'intVar' => 1234,
                    'floatVar' => 1.234,
                    'boolVar' => false,
                    'nullVar' => null,
                ],
                '<script>window["__phpvjs__"] = '
                . '{"strVar":"string value","intVar":1234,"floatVar":1.234,"boolVar":false,"nullVar":null};'
                . '</script>',
            ],
            'set with array values' => [
                [
                    'hashVar' => ['foo' => 'bar'],
                    'arrayVar' => [1, 2, 3],
                ],
                '<script>window["__phpvjs__"] = '
                . '{"hashVar":{"foo":"bar"},"arrayVar":[1,2,3]};'
                . '</script>',
            ],
            'set with object values' => [
                [
                    'jsonVar' => new class implements \JsonSerializable {
                        public function jsonSerialize(): array
                        {
                            return ['foo' => 'bar'];
                        }
                    },
                ],
                '<script>window["__phpvjs__"] = '
                . '{"jsonVar":{"foo":"bar"}};'
                . '</script>',
            ],
        ];
    }

    /**
     * Can change window variable name
     *
     * @return void
     */
    public function testCanChangeWindowVar(): void
    {
        $this->carray->setWindowVar('_php_');
        $this->carray->toJs('foo', 'bar');
        $this->assertStringStartsWith('<script>window["_php_"]', $this->carray->renderScriptTag());
    }

    /**
     * Can change window variable name from constructor
     *
     * @return void
     */
    public function testCanChangeWindowVarFromConstructor(): void
    {
        $carry = new VariableCarry('_php_');
        $carry->toJs('foo', 'bar');
        $this->assertStringStartsWith('<script>window["_php_"]', $carry->renderScriptTag());
    }

    /**
     * Can reset variables
     *
     * @return void
     */
    public function testCanResetVariables(): void
    {
        $this->carray->toJs('foo', 'bar');
        $this->carray->reset();
        $this->assertSame('', $this->carray->renderScriptTag());
    }
}
