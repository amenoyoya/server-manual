## ElectronによるローカルGUIツール作成（1日目）

Webpack + Vue + Electron を使うと、ちょっとしたGUIツールを手っ取り早く作成することができて便利である

本稿では、WEB技術を使ったクロスプラットフォームアプリケーション制作の入門として Trello 風の TODO アプリを作成していく

- Gitリポジトリ: https://github.com/amenoyoya/task-admin

### KeyWords
- **Webpack**:
    - Webpack は、モジュールバンドラの一つで、複数の JavaScript ファイルを1ファイルにパッキングすることができる
        - 実は JavaScript だけでなく、CSS や WebFont など何でもパッキング可能
    - 元々 JavaScript には、別のファイルに記述された JavaScript モジュールを読み込むという機能がないため、それを実現するモジュールバンドラの登場で大規模な JavaScript 開発が可能となった
    - また、JavaScript には EMAScript という標準規格が制定されているが、Webブラウザごとにサポート状況が異なり、それらの差異を吸収するためにも有用
        - Babel というトランスコンパイラをシームレスに使うことが可能で、どのWebブラウザでも動作する JavaScript にコンバートすることができる
- **Vue**:
    - 仮想DOMを使った JavaScript フレームワークであり、Webの見た目（View）を JavaScript で記述できるようにするもの
        - これにより HTML だけでは表現できない、アクションや見た目の変更などを表現することができる
    - 似たようなフレームワークに React や Angular などがあるが、Vue は比較的初心者に優しいフレームワークになっており、多少雑な使い方をしても何とかなる懐の深さがある
- **Electron**:
    - GitHubが開発したオープンソースのソフトウェアフレームワーク
    - Chromium と Node.js を使っており、HTML、CSS、JavaScriptのようなWeb技術で、macOS、Windows、Linuxに対応したデスクトップアプリケーションをつくることができる

### 環境構築
- Windows 10
    - 構築手順: https://github.com/amenoyoya/win-dev-tools
    - Node.js: `10.15.3`
        - Yarn PackageManager: `1.16.0`
- Ubuntu 18.04
    - 構築手順: https://yoyablog.com/post/nodejs/webpack/
    - Node.js: `13.2.0`
        - Yarn PackageManager: `1.21.0`

### プロジェクト作成
Node.js のプロジェクトを作成する

```bash
# プロジェクト名: task-admin としてディレクトリ作成
$ mkdir task-admin
$ cd task-admin

# Node.js プロジェクト初期化
## デフォルト設定で package.json 作成
$ yarn init -y

# Webpack関連のパッケージをローカルインストール
$ yarn add  webpack webpack-cli babel-loader @babel/core @babel/preset-env \
            babel-polyfill css-loader style-loader

# VueとVueのWebpack用ローダをローカルインストール
$ yarn add vue vue-loader vue-template-compiler

# Electronをローカルインストール
$ yarn add electron

# npm scripts を並列実行するためのパッケージをローカルインストール
$ yarn add concurrently
```

#### Structure
```bash
task-admin/
 |_ public/ # Electronが読み込むドキュメントディレクトリ
 |   |_ index.html # フロント画面
 |   |_ (index.js) # Webpackがバンドルして作成する JS ファイル
 |
 |_ src/ # Webpackのソースディレクトリ
 |   |_ App.vue  # Vue単一ファイルコンポーネント
 |   |_ index.js # Webpackのエントリーポイント JS ファイル
 |
 |_ main.js           # Electron実行ファイル
 |_ package.json      # Node.js パッケージ情報ファイル
 |_ webpack.config.js # Webpack設定ファイル
```

##### public/index.html
```html
<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
</head>
<body>
    <!-- id: app の要素を Vue で制御 -->
    <div id="app"></div>
    <!-- Webpack でバンドルしたJSファイルを読み込む -->
    <script src="./index.js"></script>
</body>
</html>
```

##### src/App.vue
```html
<template>
  <div>
    <p>Hello, Vue!</p>
  </div>
</template>
```

##### src/index.js
```javascript
import Vue from 'vue'; // Vue を使う
import App from './App'; // App.vue を読み込む

// IE11/Safari9用のpolyfill
// babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
import 'babel-polyfill';

new Vue({
  el: '#app', // Vueでマウントする要素
  render: h => h(App), // App.vue をレンダリング
});
```

##### main.js
```javascript
// Electronの実行に必要なモジュールを取り込む
const electron = require('electron')
const path = require('path')
const url = require('url')
const app = electron.app
const BrowserWindow = electron.BrowserWindow

// Electronのライフサイクルを定義
let mainWindow // メインウィンドウを表す変数
app.on('ready', createWindow)
app.on('window-all-closed', function() {
  if (process.platform !== 'darwin') app.quit()
})
app.on('activate', function() {
  if (mainWindow === null) createWindow()
})

// ウィンドウを作成してコンテンツを読み込む
function createWindow() {
  mainWindow = new BrowserWindow({
    width: 800, height: 600,
    // Electron 5.0.0 以降は nodeIntegration を有効化しないと Node.js を内部で実行できない
    webPreferences: {
      nodeIntegration: true
    }
  })
  mainWindow.loadURL(url.format({ // 読み込むコンテンツを指定
    pathname: path.join(__dirname, 'public', 'index.html'),
    protocol: 'file:',
    slashes: true  
  }))
  // ウィンドウが閉じる時の処理
  mainWindow.on('closed', function() {
    mainWindow = null
  })
}
```

##### webpack.config.js
```javascript
const path = require('path')
const VueLoaderPlugin = require('vue-loader/lib/plugin')

module.exports = {
  mode: 'development', // 実行モード: development => 開発, production => 本番
  entry: './src/index.js', // エントリーポイント: ソースとなる JS ファイル
  // 出力設定: => ./public/index.js
  output: {
    filename: 'index.js', // バンドル後のファイル名
    path: path.join(__dirname, 'public') // 出力先のパス（※絶対パスで指定すること）
  },
  // モジュール読み込みの設定
  module: {
    rules: [
      // .js ファイルを babel-loader でトランスコンパイル
      {
        test: /\.js$/,
        exclude: /node_modules/, // node_modules/ 内のファイルは除外
        use: [
          // babel-loader を利用
          {
            loader: 'babel-loader',
            options: {
              // @babel/preset-env の構文拡張を有効に
              presets: ['@babel/preset-env']
            }
          }
        ]
      },
      // Vue単一ファイルコンポーネント（.vue ファイル）読み込み設定
      {
        test: /\.vue$/,
        // vue-loaderを使って .vue ファイルをコンパイル
        use: [
          {
            loader: 'vue-loader',
          },
        ],
      },
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
        test: /\.(ttf|eot|woff|woff2)(\d+)?(\?v=\d+\.\d+\.\d+)?$/,
        use: [{
          loader: 'url-loader?mimetype=application/font-woff'
        }],
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
}
```

##### package.json
Webpack（ソースファイル監視とバンドル自動実行）と Electron を並列実行するための npm scripts を記述する

```json
{
  "scripts": {
    "start": "concurrently --kill-others \"webpack --watch --watch-poll\" \"electron main.js\""
  },
  ...(略)...
}
```

#### 動作確認
WebpackバンドルとElectron実行の動作確認を行う

```bash
# npm scripts: start 実行
## concurrently --kill-others "webpack --watch --watch-poll" "electron main.js"
$ yarn start
```

基本的にElectronの起動の方が早いため、Webpackバンドル完了後 `Ctrl + R` で画面更新する必要がある

![webpack-electron.png](https://yoyablog.com/post/nodejs/img/webpack-electron.png)
