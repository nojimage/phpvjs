<?php

/*
 * Copyright (c) 2023 Takashi Nojima
 */

declare(strict_types=1);

namespace Nojimage\PHPvJS;

/**
 * Implementation of Variable Carry Interface
 */
class VariableCarry implements VariableCarryInterface
{
    /**
     * @param string $windowVar the JavaScript root variable name
     */
    public function __construct(string $windowVar = '__phpvjs__')
    {
        $this->setWindowVar($windowVar);
    }

    /**
     * the pass variables
     *
     * @var array<string, \JsonSerializable|int|float|string|bool|array|null>
     */
    private $vars = [];

    /**
     * the JavaScript root variable name
     *
     * @var string
     */
    private $windowVar = '__phpvjs__';

    /**
     * Set variables to JavaScript
     *
     * @param array $data the pass data ['key' => 'value', ...]
     * @return void
     */
    public function setJsData(array $data): void
    {
        foreach ($data as $key => $value) {
            $this->toJs($key, $value);
        }
    }

    /**
     * Set a variable to JavaScript
     *
     * @param string $key the name of variable
     * @param \JsonSerializable|int|float|string|bool|array|null $value the pass value
     * @return void
     */
    public function toJs(string $key, $value): void
    {
        $this->vars[$key] = $value;
    }

    /**
     * Render script tag for pass variables
     *
     * @return string
     * @throws \JsonException
     */
    public function renderScriptTag(): string
    {
        if (!count($this->vars)) {
            return '';
        }

        // escape `<`, `>`, `&`, `'`, `"` so that neither the variable name nor
        // the values can alter how the HTML parser tokenizes the script element
        $flags = \JSON_HEX_TAG | \JSON_HEX_AMP | \JSON_HEX_APOS | \JSON_HEX_QUOT | \JSON_THROW_ON_ERROR;

        // json_encode() of a string yields the surrounding double quotes too
        $name = json_encode($this->windowVar, $flags);
        $json = json_encode($this->vars, $flags);

        return sprintf('<script>window[%s] = %s;</script>', $name, $json);
    }

    /**
     * Set the JavaScript root variable name
     *
     * @param string $windowVar the JavaScript root variable name
     * @return void
     */
    public function setWindowVar(string $windowVar): void
    {
        if ($windowVar === '') {
            throw new \InvalidArgumentException('windowVar must be a non-empty string');
        }

        $this->windowVar = $windowVar;
    }

    /**
     * Reset variables
     *
     * @return void
     */
    public function reset(): void
    {
        $this->vars = [];
    }

    /**
     * @inheritDoc
     */
    public function getAttributeName(): string
    {
        return self::class;
    }
}
