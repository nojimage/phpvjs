<?php

/*
 * Copyright (c) 2023 nojimage
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
     * @throws \InvalidArgumentException
     */
    public function renderScriptTag(): string
    {
        if (!count($this->vars)) {
            return '';
        }

        // for PHP < 7.3 check JSON_THROW_ON_ERROR
        $json = defined('JSON_THROW_ON_ERROR')
            ? json_encode($this->vars, \JSON_THROW_ON_ERROR)
            : json_encode($this->vars);
        if ($json === false) {
            throw new \InvalidArgumentException(json_last_error_msg(), json_last_error());
        }

        return sprintf('<script>window["%s"] = %s;</script>', $this->windowVar, $json);
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
