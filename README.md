# PHPvJS Middleware

![License](https://img.shields.io/github/license/nojimage/phpvjs)
![Packagist Version (custom server)](https://img.shields.io/packagist/v/nojimage/phpvjs)
[![Build Status](https://github.com/nojimage/phpvjs/actions/workflows/ci.yml/badge.svg)](https://github.com/nojimage/phpvjs/actions/workflows/ci.yml)

## Overview

This project is a middleware that allows passing PHP variables to JavaScript in a PSR-7 and PSR-15 compliant manner. It is designed to be used with frameworks that have a PSR-15 middleware stack.

## Requirements

- PHP 7.2 or above
- Framework with PSR-15 compliant middleware stack

## Installation

```shell
composer require nojimage/phpvjs
```

Add it to your PSR-15 middleware stack. Here's an example for CakePHP 4.x:

```php
// src/Application.php
public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
{
    // ...
    $middlewareQueue->add(new \Nojimage\PHPvJS\PHPvJSMiddleware());
    // ...
};
```

## Usage

You can pass PHP variables to JavaScript using an instance of `\Nojimage\PHPvJS\VariableCarry`, which can be obtained from the request object attribute.

```php
/*
 * @var \Psr\Http\Message\ServerRequestInterface $request 
 */

/** @var \Nojimage\PHPvJS\VariableCarry $carry */
$carry = $request->getAttribute(\Nojimage\PHPvJS\VariableCarry::class);
$carry->toJs('foo', $somevalue);
```

On the JavaScript side, you can access the PHP variables through the `window.__phpvjs__` object.

```js
console.log(window.__phpvjs__);
// or
const phpvjs = window.__phpvjs__ ?? {};
```

## Contributing

If you find any bugs or have suggestions for new features, please create an issue or submit a pull request.

## License

This project is released under the MIT License.
