<?php

/*
 * Copyright (c) 2023-2025 Takashi Nojima
 */

declare(strict_types=1);

namespace Nojimage\PHPvJS\Test\TestCase;

use Nojimage\PHPvJS\VariableCarry;
use PHPUnit\Framework\Attributes\DataProvider;
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
     * @return void
     */
    #[DataProvider('dataRenderScriptTag')]
    public function testRenderScriptTag(array $data, string $expects): void
    {
        $this->carray->setJsData($data);
        $this->assertSame($expects, $this->carray->renderScriptTag());
    }

    /**
     * @return array[]
     */
    public static function dataRenderScriptTag(): array
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
            // the closing tag must not terminate the script element
            'set with a value containing a script closing tag' => [
                [
                    'xssVar' => '</script><img src=x onerror=alert(1)>',
                ],
                '<script>window["__phpvjs__"] = '
                . '{"xssVar":"\u003C\/script\u003E\u003Cimg src=x onerror=alert(1)\u003E"};'
                . '</script>',
            ],
            // `<!--<script>` puts the HTML tokenizer into the script data double escaped state,
            // where the closing tag is no longer recognized
            'set with a value containing an escaping text span start' => [
                [
                    'xssVar' => '<!--<script>',
                ],
                '<script>window["__phpvjs__"] = '
                . '{"xssVar":"\u003C!--\u003Cscript\u003E"};'
                . '</script>',
            ],
            'set with a value containing quotes and ampersand' => [
                [
                    'quotVar' => 'say "hi"',
                    'aposVar' => "it's",
                    'ampVar' => 'a&b',
                ],
                '<script>window["__phpvjs__"] = '
                . '{"quotVar":"say \u0022hi\u0022","aposVar":"it\u0027s","ampVar":"a\u0026b"};'
                . '</script>',
            ],
            'set with a key containing a script closing tag' => [
                [
                    '</script>' => 'value',
                ],
                '<script>window["__phpvjs__"] = '
                . '{"\u003C\/script\u003E":"value"};'
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
