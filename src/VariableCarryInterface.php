<?php

/*
 * Copyright (c) 2023 Takashi Nojima
 */

namespace Nojimage\PHPvJS;

/**
 * Variable Carry Interface
 */
interface VariableCarryInterface
{
    /**
     * Set a variable to JavaScript
     *
     * @param string $key the name of variable
     * @param \JsonSerializable|int|float|string|bool|array|null $value the pass value
     * @return void
     */
    public function toJs(string $key, $value): void;

    /**
     * Render script tag for pass variables
     *
     * @return string
     * @throws \JsonException
     * @throws \InvalidArgumentException
     */
    public function renderScriptTag(): string;

    /**
     * Reset variables
     *
     * @return void
     */
    public function reset(): void;

    /**
     * Returns the ServerRequest attribute name
     *
     * @return string
     */
    public function getAttributeName(): string;
}
