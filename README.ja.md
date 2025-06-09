# PHPvJS Middleware

このライブラリは、API を作成することなく、Vue などの JavaScript フレームワークに値を渡しやすくすることを目的としています。 これは、マルチページ アプリケーション (MPA) で Web アプリケーションを迅速に開発する場合に特に役立ちます。

このプロジェクトは、PHP 変数を JavaScript に渡すことを可能にするミドルウェアを提供します。 PSR-15に準拠するミドルウェアスタックを備えたフレームワークで使用するように設計されています。

## 必要要件

- PHP 8.2以上
- PSR-15対応のミドルウェアスタックを持つフレームワーク

## インストール

```shell
composer require nojimage/phpvjs
```

PSR-15対応のミドルウェアスタックに追加する

例: CakePHP 5.x

```php
// src/Application.php
    public function middleware(MiddlewareQueue $middlewareQueue): MiddlewareQueue
    {
        // ...
        $middlewareQueue->add(new \Nojimage\PHPvJS\PHPvJSMiddleware());
    }
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

### Vue.js での使用例

エントリーポイントとなるファイルで

```js
import { createApp } from 'vue';

const app = createApp({
    data() {
        // PHPvJSを通じてセットされた値を利用する
        return window.__phpvjs__ ?? {};
    },
    // ... other options
});

// ...other setups

app.mount("#app");
```
