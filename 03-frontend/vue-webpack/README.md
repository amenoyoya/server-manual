# Webエンジニアのため基礎フロントエンド開発入門

## 前書き

最近のフロントエンド開発は流行り廃りが激しく、普段バックエンド開発しか行っていない私のような人間には、なかなか辛いものがあります

そのため手始めに、比較的難易度の低いと思われる **Vue + Webpack** を使ったフロントエンド開発に入門してみたいと思います

### 対象読者
- Node.js や Webpack についてよくわかっていない方
- 最近のフロントエンド開発に、なんとなく入門してみたい方
- vue-cli を使わず最小構成の Vue 開発を行ってみたい方

***

## 最近のフロントエンド開発について

私が HTML や CSS, JavaScript を触り始めたとき、JavaScript はWebページにちょっとした動きを実装するだけのものでしかありませんでした

しかし、HTML5 が登場し、JavaScript も ECMAScript という標準規格が策定された昨今では、JavaScript がフロントエンド開発の主役になってきていると感じます

特に、React や Vue, Angular などの仮想DOMを用いた JavaScript フレームワークの台頭により、フロントエンド開発において Node.js を使うことはほぼ必須と言っても良い状況です

### Node.js とは
元々 JavaScript はWebブラウザ上で動作する言語ですが、それをサーバサイド（バックエンド）で動作するように開発されたのが Node.js です

フロントエンド開発に有用なツールはほとんどが Node.js 製のものであるため、Node.js を使えるようにしておくことは必須と言っても良いです

例えばどういったツールがあるのかというと、以下のようなものです

- SASS, SCSS といった CSS の拡張言語を CSS にコンパイルするコンパイラ
- 複数ファイルに分割された JavaScript を一つにパッキングして、Webページの読み込み速度を向上させるバンドラ
- ファイルの変更を監視して、自動的にブラウザをリロードする自動化ツール

他にも様々なツールが Node.js で作られており、非常に便利です

最近では、Express.js を代表とする多くのWebアプリケーションフレームワークが活発に開発されており、バックエンドからフロントエンドまですべて JavaScript で実装することもできるようになってきています

### Webpack とは
Webpack は、モジュールバンドラの一つで、複数の JavaScript ファイルを1ファイルにパッキングすることができます（実は JavaScript だけでなく、CSS や WebFont など何でもパッキングできます）

元々 JavaScript には、別のファイルに記述された JavaScript モジュールを読み込むという機能がないため、それを実現するモジュールバンドラの登場で大規模な JavaScript 開発が可能になりました

また、JavaScript には ECMAScript という標準規格が制定されていますが、Webブラウザごとにサポート状況が異なります

それらの差異を吸収するために Babel というトランスコンパイラ（JavaScript を JavaScript にコンパイルするコンパイラ）がよく使われるのですが、Webpack を使うことで ECMAScript をよりシームレスに使うことも可能です

### Vue とは
仮想DOMを使った JavaScript フレームワークであり、Webの見た目（View）を JavaScript で記述できるようにするものです

これにより HTML だけでは表現できない、アクションや見た目の変更などを表現することができます

似たようなフレームワークに React や Angular などがありますが、Vue は比較的初心者に優しいフレームワークになっており、多少雑な使い方をしても何とかなる懐の深さがあります

#### Vue と jQuery の比較
一昔前は jQuery が流行っており、動的なコンテンツを表現する場合はとりあえず jQuery 使っておけばOKという雰囲気がありました

しかし jQuery はあくまでもDOM（HTMLの構造）を操作するだけのものであり、Webページ全体の管理・制御をするにはあまり向いていませんでした

そうした中で出てきたのが Vue をはじめとした JavaScript フレームワークです

これらは、HTMLテンプレートも JavaScript 側で面倒を見ることができるため、Webページ全体を管理・制御するのに向いています

逆に言えば、ちょっとしたアクションをしたいだけ、といった場合には jQuery の方が簡単であることも多いです

なかなか言葉では分かりにくいと思いますので、ここで Vue と jQuery のコードを具体的に比較してみましょう

例えば、ボタンを押すとカウントが +1 されるという機能を jQuery で実装すると以下のようになります

<p class="codepen" data-height="265" data-theme-id="dark" data-default-tab="js,result" data-user="amenoyoya" data-slug-hash="rNaGKVy" style="height: 265px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; border: 2px solid; margin: 1em 0; padding: 1em;" data-pen-title="rNaGKVy">
  <span>See the Pen <a href="https://codepen.io/amenoyoya/pen/rNaGKVy">
  rNaGKVy</a> by Ameno Yoya (<a href="https://codepen.io/amenoyoya">@amenoyoya</a>)
  on <a href="https://codepen.io">CodePen</a>.</span>
</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

上記のように、動的に変更したいHTMLタグを id 等をもとに指定し、その構造・属性等を変更することで動的コンテンツを表現します

一方で Vue で同じ機能を実装しようとする場合、以下のようになります

<p class="codepen" data-height="265" data-theme-id="dark" data-default-tab="js,result" data-user="amenoyoya" data-slug-hash="XWJeYzV" style="height: 265px; box-sizing: border-box; display: flex; align-items: center; justify-content: center; border: 2px solid; margin: 1em 0; padding: 1em;" data-pen-title="CountUp test by Vue">
  <span>See the Pen <a href="https://codepen.io/amenoyoya/pen/XWJeYzV">
  CountUp test by Vue</a> by Ameno Yoya (<a href="https://codepen.io/amenoyoya">@amenoyoya</a>)
  on <a href="https://codepen.io">CodePen</a>.</span>
</p>
<script async src="https://static.codepen.io/assets/embed/ei.js"></script>

上記 HTML を見ると、通常のHTMLタグでは見慣れない記法が使われていることがわかります

例えば `@click` 属性により、Vue（JavaScript）で定義されたメソッドを button の onClick イベントに紐づけることが可能です

また Vue 側で保持しているデータ（`count`）をHTMLテンプレートに埋め込むために `{{count}}` という記法を使うことも可能です

パッと見、Vue の方が記述量が増えており、煩雑になっているようにも見えますが、HTMLごと Vue で管理することができるため、Webページ（Webアプリケーション）の規模が大きくなるほど、Vue の方が記述しやすくなっていきます

***

## Node.js 開発環境構築

それでは Node.js の開発環境を構築していきます

[LinuxNight](/release/) を使っている場合は、Linuxbrew が標準インストールされていますので、以下のコマンドで Node.js がインストールできます

```bash
# Linuxbrew で Node.js インストール
$ brew install node

# Node.js バージョン確認
$ node -v
v13.5.0

# ついでに yarn パッケージマネージャをインストールしておく
$ npm i -g yarn

# yarn バージョン確認
$ yarn -v
1.21.1
```

Node.js には npm というパッケージマネージャが標準でついておりますが、yarn の方が高速に動作するため、筆者はよく yarn をパッケージマネージャとして使っております

### Node.js で Hello, world
以上で Node.js が使えるようになりましたので、動作確認を兼ねて `Hello, world` を表示してみましょう

ファイル名は任意ですが、ここでは `hello.js` というファイルを作成し、以下のように JavaScript コードを記述しましょう

{{<code lang="javascript" title="hello.js">}}
// コンソールに "Hello, world" と表示
console.log('Hello, world');
{{</code>}}

作成したら、以下のコマンドで Node.js を実行します

```bash
# Node.js で hello.js ファイルを実行する
$ node hello.js
Hello, world
```

### yarn でパッケージをインストールしてみる
Node.js には便利なパッケージが多くあります

ここでは、コンソールに色をつけることのできる `colors` というパッケージを yarn でインストールしてみましょう

```bash
# yarn で colors パッケージインストール
$ yarn add colors

## 標準パッケージマネージャの npm を使う場合は以下のコマンド
## $ npm install colors
```

上記コマンドを実行すると、カレントディレクトリに `node_modules` ディレクトリが作成されます

このディレクトリ内に各種パッケージがインストールされるという仕組みになっています

なお、一緒に `package.json` というファイルも作成されますが、このファイルにはインストールされたパッケージの情報が記述されています

このファイルがあれば `node_modules` ディレクトリを削除しても `package.json` の情報をもとに、使われていたパッケージを再インストールすることができます

yarn の場合であれば以下のコマンドです

```bash
# package.json の情報をもとにパッケージを一括インストール
$ yarn install
```

話がそれてしまいましたが、インストールした colors パッケージを使って、コンソールに色付き文字を表示してみましょう

{{<code lang="javascript" title="hello.js">}}
// Node.js では require 関数を使って、別ファイルに記述された JavaScript モジュール（パッケージ）を読み込める
// colors パッケージを読み込む
require('colors');

// 黄色テキストで "Hello, world" 表示
console.log('Hello, world'.yellow);
{{</code>}}

これで `node hello.js` を実行すると、黄色テキストで `Hello, world` と表示されるはずです

#### パッケージのグローバルインストールとローカルインストール
今回、パッケージは作業ディレクトリにローカルインストールしましたが、どの JavaScript プログラムからも読み込むことができるようにグローバルインストールすることも可能です

yarn でグローバルインストールする場合は `global` オプションをつけます

```bash
# colors パッケージをグローバルインストール
## 別ディレクトリにあるプログラムからでも require('colors') できるようにする
$ yarn global add colors
```

ただし、基本的にグローバルインストールは推奨されていません

これは、グローバルインストールしてしまうと、他の開発環境において同じパッケージ環境を再現するのが難しくなってしまうからです

ローカルインストールであれば、`package.json`ファイルを共有するだけで、同じパッケージ環境を再現できるため、特別な理由がない限りローカルインストールするように推奨しておきます

***

## Webpack 開発環境構築

Vue は通常の JavaScript と同様にHTML側で `<script>` タグを用いて使うこともできますが、Webpack 等のモジュールバンドラと一緒に使った方が、単一ファイルコンポーネント（`.vue` ファイルで定義されるコンポーネント）機能を使うことができるなどの利点が多いです

そのため、Vue + Webpack 環境を手っ取り早く構築するためのツールとして `vue-cli` のような環境自動構成ツールが存在しています

しかし、環境自動構成ツールを用いて環境構築すると、不要な機能もインストールされたりするため、個人的にはあまり好きではありません

また、本稿は学習を目的にしていることもあり、必要最低限の Vue + Webpack 環境を構築していきたいと思います

### Webpack + Babel 環境構築
まずは、Webpack と Babel をインストールしましょう

Babel を使うことで ECMAScript（以下 ES）2015 以上の JavaScript を、ブラウザ間の差異を気にせずに使うことができるようになります

また、Webpack を使うことで ES Module 機能（別の JS ファイルを読み込む機能）を実現することも可能です（分割されたモジュールを1つにまとめるモジュールバンドリング）

なお、ここで使う Webpack は、本稿執筆時点で最新の 4系（4.41.5）を想定しています

```bash
# Webpack系のパッケージと Babel系の必要なパッケージをインストール
$ yarn add webpack webpack-cli babel-loader @babel/core @babel/preset-env babel-polyfill
```

なお、この辺りのパッケージは更新頻度が高く、本稿執筆時点で `@babel/core` などは既に古いパッケージとなっているという話もあります

しかし、筆者がフロントエンド開発最前線の情報に疎いため、本稿ではこのパッケージを使っていきたいと思います

#### webpack.config.js
続いて、Webpack の設定ファイルである `webpack.config.js` を記述していきましょう

この設定ファイルが「何書いているのかよく分からない」ということで Webpack を敬遠してしまう人も多いと聞きます

そのため、ここではコメントを多めに入れております

{{<code lang="javascript" title="webpack.config.js">}}
// 絶対パスを記述する際などに必要なため path パッケージを使う
const path = require('path');

module.exports = {
  // 実行モード: development => 開発, production => 本番
  // production を指定すると、パッキング後のファイルサイズを削減し、より早く JS ファイルを読み込めるようになる
  // webpack4系以降はmodeを指定しないと警告が出る
  mode: 'development',

  // エントリーポイント: ソースとなる JS ファイル
  // ここで指定した JS ファイルに、必要なモジュールをすべて読み込む想定
  entry: './src/index.js',
  
  // 出力設定
  output: {
    // バンドル後のファイル名
    filename: 'bundle.js',
    // 出力先のパス（※絶対パスで指定すること）
    path: path.join(__dirname, 'public')
  },

  // => ここまでの設定で ./src/index.js（と関連モジュール）が ./public/bundle.js にパッキングされる

  // モジュール読み込みの設定
  module: {
    rules: [
      // JavaScript（.js ファイル）読み込み設定
      {
        test: /\.js$/, // 指定正規表現にマッチするファイルを読み込む
        // ファイル読み込み時に使うローダーを指定
        use: [
          // babel-loader: ES2015以上の JavaScript をすべてのブラウザで使える JavaScript にトランスコンパイルするローダー
          {
            loader: 'babel-loader',
            // babel-loader のオプション
            options: {
              // @babel/preset-env をプリセットとして使うといい感じの JavaScript にトランスコンパイルしてくれる
              presets: ['@babel/preset-env']
            }
          },
        ]
      },
    ]
  },
};
{{</code>}}

#### Webpack 動作確認
それでは、ES2015 以上の JavaScript のコードを書いて、Webpack でトランスコンパイルしてみましょう

まず、バンドル後の JavaScript ファイル（`bundle.js`）を読み込むだけの HTML を `./public/index.html` に作成します

{{<code lang="html" title="./public/index.html">}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>動作確認</title>
</head>
<body>
    <!-- Webpack でバンドルされた bundle.js を読み込む -->
    <script src="bundle.js"></script>
</body>
</html>
{{</code>}}

続いて、エントリーポイントに設定した `./src/index.js` と、モジュール用の `./src/mylib.js` を作成しましょう

{{<code lang="javascript" title="./src/mylib.js">}}
// ES2015 以上の JavaScript はアロー関数が使える
const hello = () => {
  alert("こんにちは！Webpack");
};

// hello関数を export
export {
  hello
};
{{</code>}}

{{<code lang="javascript" title="./src/index.js">}}
// ES Module 機能を使い mylib.js を読み込む
import { hello } from "./mylib";

// IE11/Safari9用のpolyfill
// babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
import 'babel-polyfill';

// mylib.jsに定義された hello 関数を実行
hello();
{{</code>}}

ここまでで、プロジェクトディレクトリは以下のようになっているはずです

```bash
./
|_ node_modules/ # Node.js の各種パッケージがインストールされている
|
|_ public/
|   |_ index.html # bundle.js を読み込む HTML
|
|_ src/
|   |_ index.js # エントリーポイント（メインソースファイル）
|   |_ mylib.js # index.js から読み込まれるモジュール
|
|_ package.json # インストールしたパッケージ情報等が記述されている
|_ webpack.config.js # Webpack バンドル設定
|_ yarn.lock # yarn パッケージマネージャを使っていると作られる lock ファイル
```

問題なければ、Webpack を使って JavaScript ファイルをバンドルしてみましょう

```bash
# yarn webpack で ./node_modules/.bin/webpack が実行される
$ yarn webpack

## => webpack.config.js の設定に従って Webpack が実行される
## => ./public/bundle.js が作成されるはず
```

バンドル済みファイル `./public/bundle.js` が作成されたら `./public/index.html` をWebブラウザで開いてみましょう

![webpack-test.png](/post/nodejs/img/webpack-test.png)

"こんにちは！Webpack" というアラートが表示されたら成功です

### Webpack開発サーバの導入
今のままでは、ファイルを修正したりした際、毎回 Webpack を実行しブラウザで確認するという作業をしなければなりません

特にフロントエンド開発では、デザインやレイアウトの修正が頻繁に行われるため、ファイルの変更を監視して自動的にコンパイル＆ブラウザリロードできると便利です

Webpack にもこういった自動化ツールが存在しますので導入しておきましょう

```bash
# Webpack開発サーバをインストール
$ yarn add webpack-dev-server
```

`webpack-dev-server` をインストールしたら、`webpack.config.js` に設定を追加します

{{<code lang="javascript" title="webpack.config.js">}}
// ...(略)...
module.exports = {
  // ...(略)...

  // 開発サーバー設定
  devServer: {
    // 起点ディレクトリを ./public/ に設定 => ./public/index.html がブラウザで開かれる
    contentBase: path.join(__dirname, 'public'),
    
    // ポートを3000に設定
    // 重複しないポートを指定すること
    port: 3000,
  },
};
{{</code>}}

ここまで設定したら webpack-dev-server を起動します

```bash
# Webpack開発サーバをホットリロード対応モードで実行
$ yarn webpack-dev-server --hot
```

この状態で {{<exlink href="http://localhost:3000">}} にアクセスすると `./public/index.html` の内容が表示されるはずです

これだけでは何が良いのかよく分からないと思いますが、`./src/index.js`（および関連するモジュールファイル）に変更を加えてみましょう

すると、起動中の開発サーバが自動的にファイルの変更を検知して、Webpack を実行 => ブラウザをリロードしてくれます（便利😍）

### npm script を書いてみる
Webpack開発サーバは `webpack-dev-server` という長いコマンドを打たなければならないので結構面倒です

こういった場合には **npm script** を書くと便利です

npm script は `package.json` の `scripts` 項目に定義するコマンドスクリプトです

以下のように記述してみましょう

{{<code lang="json" title="package.json">}}
{
  "scripts": {
    "watch": "webpack-dev-server --hot"
  },
  "dependencies": {
    ...(略)...
  }
}
{{</code>}}

上記のように scripts を記述すると `yarn watch` コマンドで `yarn webpack-dev-server --hot` を呼び出すことができます

```bash
# `yarn watch` で `yarn webpack-dev-server --hot` コマンドを間接実行
$ yarn watch
```

***

## Vue 開発環境構築

続いて Vue 開発環境を構築していきましょう

### Vue と VueLoader のインストール
```bash
# vue, vue-loader インストール
$ yarn add vue vue-loader vue-template-compiler
```

インストールしたら `webpack.config.js` に設定を追加します

{{<code lang="javascript" title="webpack.config.js">}}
// 絶対パスを記述する際などに必要なため path パッケージを使う
const path = require('path');
// vue-loader plugin を使う
const VueLoaderPlugin = require('vue-loader/lib/plugin'); 

module.exports = {
  // ...(略)...

  // モジュール設定
  module: {
    rules: [
      // JavaScript（.js ファイル）読み込み設定
      {
        // ...(略)...
      },

      // Vue単一ファイルコンポーネント（.vue ファイル）読み込み設定
      {
        // 拡張子 .vue の場合
        test: /\.vue$/,
        // vue-loaderを使って .vue ファイルをコンパイル
        use: [
          {
            loader: "vue-loader",
          },
        ],
      },
    ]
  },
  
  // import文で読み込むモジュールの設定
  resolve: {
    extensions: [".js", ".vue"], // .js, .vue をimport可能に
    modules: ["node_modules"], // node_modulesディレクトリからも import できるようにする
    alias: {
      // vue-template-compilerに読ませてコンパイルするために必要な設定
      vue$: 'vue/dist/vue.esm.js',
    },
  },

  // VueLoaderPluginを使う
  plugins: [new VueLoaderPlugin()],

  // 開発サーバー設定
  // ...(略)...
};
{{</code>}}

### Vue の動作確認
設定が完了したら Vue の動作確認を行いましょう

Vue について詳しいことは、ここでは解説しないので {{<exlink href="https://jp.vuejs.org/v2/guide/index.html" str="公式サイト">}} を参照してください

とりあえず以下のようにプロジェクトを構成します

```bash
./
|_ node_modules/
|
|_ public/
|   |_ index.html
|   |_ bundle.js # -- Webpack が作成するファイル
|
|_ src/
|   |_ App.vue # index.js から読み込まれる Vue単一ファイルコンポーネント
|   |_ index.js
|
|_ package.json
|_ webpack.config.js
|_ yarn.lock
```

{{<code lang="html" title="./public/index.html">}}
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Vue 動作確認</title>
</head>
<body>
    <!-- Vueにより id="app" の要素が置き換えられる -->
    <div id="app"></div>
    
    <!-- Webpack でバンドルされた bundle.js を読み込む -->
    <script src="bundle.js"></script>
</body>
</html>
{{</code>}}

{{<code lang="html" title="./src/App.vue">}}
<template>
  <div>
    <p>Hello, Vue!</p>
  </div>
</template>
{{</code>}}

{{<code lang="javascript" title="./src/index.js">}}
import Vue from 'vue'; // Vue を使う
import App from './App'; // App.vue を読み込む

// IE11/Safari9用のpolyfill
// babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
import 'babel-polyfill';

new Vue({
  el: '#app', // Vueでマウントする要素
  render: h => h(App), // App.vue をレンダリング
});
{{</code>}}

動作確認用にプロジェクトを構成したら、Webpack開発サーバを起動しましょう

```bash
$ yarn watch
```

{{<exlink href="http://localhost:3000">}} にアクセスして "Hello, Vue!" と表示されたらOKです

### Vuetify を使ってみる
せっかくなので、Webpack が CSS や Icon などもパッキングできることを確認しておきましょう

動作確認用に今回は **Vuetify** を使ってみます

Vuetify は、Googleが提唱したマテリアルデザインの考えにのっとって構成された Vue ベースの UI コンポーネントで、格好いいデザインのインタフェースを手軽に作成することができます

```bash
# CSS, Icon 等を Webpack で読み込むためのローダーをインストール
$ yarn add css-loader style-loader url-loader

# Vuetify インストール
$ yarn add vuetify

# フォントアイコン類をインストール
$ yarn add material-design-icons-iconfont @fortawesome/fontawesome-free
```

各種パッケージをインストールしたら `webpack.config.js` に設定を追加します

{{<code lang="javascript" title="webpack.config.js">}}
// ...(略)...

module.exports = {
  // ...(略)...

  // モジュール設定
  module: {
    rules: [
      // ...(略)...
      
      // スタイルシート（.css ファイル）読み込み設定
      {
        // .css ファイル: css-loader => vue-style-loader の順に適用
        // - css-loader: cssをJSにトランスコンパイル
        // - style-loader: <link>タグにスタイル展開
        test: /\.css$/,
        use: ['style-loader', 'css-loader']
      },

      /* アイコンローダーの設定 */
      {
        test: /\.svg(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'url-loader?mimetype=image/svg+xml'
        }],
      },
      {
        test: /\.woff(\d+)?(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'url-loader?mimetype=application/font-woff'
        }],
      },
      {
        test: /\.eot(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'url-loader?mimetype=application/font-woff'
        }],
      },
      {
        test: /\.ttf(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'url-loader?mimetype=application/font-woff'
        }],
      },
    ]
  },

  // ...(略)...
};
{{</code>}}

設定したら、ソーススクリプトを修正し、Vuetify に対応させましょう

{{<code lang="html" title="./src/App.vue">}}
<template>
    <!-- Vuetifyコンポーネントを使う場合は v-appタグで囲むこと！ -->
    <v-app>
        <v-content>
            <!-- Alertコンポーネントを使ってみる -->
            <v-alert :value="true" type="success">Hello, Vuetify!</v-alert>
        </v-content>
    </v-app>
</template>
{{</code>}}

{{<code lang="javascript" title="./src/index.js">}}
import Vue from 'vue'; // Vue を使う
import App from './App'; // App.vue を読み込む

// IE11/Safari9用のpolyfill
// babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
import 'babel-polyfill';

// vuetify を使う
import Vuetify from 'vuetify';
// vuetify のスタイルシートを読み込む
import 'vuetify/dist/vuetify.min.css';
// material-design-icons を読み込む
import 'material-design-icons-iconfont/dist/material-design-icons.css';

Vue.use(Vuetify); // Vuetifyのコンポーネントを使用可能に

new Vue({
  el: '#app', // Vueでマウントする要素
  vuetify: new Vuetify(), // Vuetify を使う
  render: h => h(App) // App.vue をレンダリング
});
{{</code>}}

修正したら `webpack-dev-server` を起動し {{<exlink href="http://localhost:3000">}} を確認してください

![vuetify-test.png](/post/nodejs/img/vuetify-test.png)

いかがでしょうか？

これでとりあえず Webpack を使ったフロントエンド開発ができるようになりました👌

***

## 依存パッケージのアップデート

Node.jsで開発していると、依存パッケージが高い頻度で更新されて困ることが多いです

特にGitHubリポジトリでコード管理していると、「このパッケージは脆弱性があります、そのパッケージは推奨されません」という親切なセキュリティアラートで埋め尽くされることが度々あります

そういった場合に、一つ一つのパッケージの最新バージョンを調べてアップデートしていくのは面倒であるため、yarn パッケージマネージャの `upgrade` コマンドを使うと便利です

### 自動アップデート
以下のコマンドを使うと **package.json に記述されたバージョンの範囲で**依存パッケージを一括で自動アップデートできます

```bash
# 全パッケージを一括アップデート
$ yarn upgrade

# 特定のパッケージをアップデート
$ yarn upgrade <パッケージ名>

# 指定パッケージを指定タグ（バージョン）にアップデート
$ yarn upgrade <パッケージ名>@<タグ名｜バージョン名>
```

なお、**package.json に記述されたバージョン範囲を無視して**アップデートする場合は `--latest` オプションを指定しましょう

```bash
# package.json の指定バージョンを無視して最新版にアップデート
$ yarn upgrade --lateset
```

### 対話的アップデート
どのパッケージがどのバージョンにアップデートされるか確認しながらアップデートしたい場合は `upgrade-interactive` が便利です（オプション引数は `upgrade` と同様）

```bash
# --latestオプション: pakage.json の指定バージョンを無視して最新版にアップデート
$ yarn upgrade-interactive [--latest] [パッケージ名[@タグ名｜バージョン名]]
```
