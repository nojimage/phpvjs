# PHPvJS Middleware

PHP変数をJavaScriptに渡す PSR-7, PSR-15対応ミドルウェア

## 必要要件

- PHP 7.2以上
- PSR-15対応のミドルウェアスタックを持つフレームワーク

## インストール

```shell
composer require nojimage/phpvjs
```

PSR-15対応のミドルウェアスタックに追加する

例: CakePHP 4.x

```php
// src/Application.php
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // ...
        $middlewareQueue->add(new \Nojimage\PHPvJS\PHPvJSMiddleware());
    };
```

## 使い方

リクエストオブジェクトの属性から取得できる `\Nojimage\PHPvJS\VariableCarry` のインスタンスを利用して、PHP変数をJavaScriptに渡すことができます。

```php
/*
 * @var \Psr\Http\Message\ServerRequestInterface $request 
 */

/** @var \Nojimage\PHPvJS\VariableCarry $carry */
$carry = $request->getAttribute(\Nojimage\PHPvJS\VariableCarry::class);
$carry->toJs('foo', $somevalue);
```

JavaScript側では、`window.__phpvjs__`オブジェクトからPHP変数を取得できます。

```js
console.log(window.__phpvjs__);
// または
phpvjs = window.__phpvjs__ ?? {};
```
