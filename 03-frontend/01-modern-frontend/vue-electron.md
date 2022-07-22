# Vue と Electron で手軽に入門する最新フロントエンド技術

## 前書き

Vue + Webpack + Electron を使うと、ちょっとしたGUIツールを手っ取り早く作成することができて便利です

一昔前はGUIツールを作ろうと思うと、C言語で Win32API を使ったり Gtk を使ったりなど、結構面倒なプログラミングが必要でした

しかし、WEB技術の発展（というより JavaScript の発展）に伴って、ローカルGUIアプリケーションもWEB技術の応用で作成できるようになってきました

本稿では、WEB技術を使ったクロスプラットフォームアプリケーション制作の入門として Trello 風の TODO アプリを作成していくことにします

### 対象読者
- あったら便利なツールのアイディアはあるけれど、GUIツールの作成に二の足を踏んでいる方
- WEBフロントエンドの技術はあるけれど、ローカルアプリを作ったことはない方
- TODO アプリが欲しい方

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

***

## 環境構築

### Environment
- Windows 10
    - 構築手順: {{<exlink href="https://github.com/amenoyoya/win-dev-tools">}}
    - Node.js: `12.14.1`
        - Yarn PackageManager: `1.21.0`
- Ubuntu 18.04
    - 構築手順: [最小構成の Vue + Webpack で始める今時のフロントエンド開発](/post/nodejs/webpack/)
    - Node.js: `13.2.0`
        - Yarn PackageManager: `1.21.1`

### プロジェクト作成
Electron のプロジェクトを作成します

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

# sass, scss のコンパイラを導入
$ yarn add sass-loader node-sass

# VueとVueのWebpack用ローダをローカルインストール
$ yarn add vue vue-loader vue-template-compiler

# Electronをローカルインストール
$ yarn add electron

# npm scripts を並列実行するためのパッケージをローカルインストール
$ yarn add concurrently
```

### 構成
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

{{<code lang="html" title="public/index.html">}}
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
{{</code>}}

{{<code lang="html" title="src/App.vue">}}
<template>
  <div>
    <p>Hello, Vue!</p>
  </div>
</template>
{{</code>}}

{{<code lang="javascript" title="src/index.js">}}
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

{{<code lang="javascript" title="main.js">}}
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
{{</code>}}

{{<code lang="javascript" title="webpack.config.js">}}
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
      // Sass（.scss ファイル）コンパイル設定
      {
        // sass-loader => css-loader => vue-style-loader の順に適用
        // vue-style-loader を使うことで .vue ファイル内で <style lang="scss"> を使えるようになる
        test: /\.scss$/,
        use: ['vue-style-loader', 'css-loader', 'sass-loader'],
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
{{</code>}}

Webpack（ソースファイル監視とバンドル自動実行）と Electron を並列実行するための npm scripts を `package.json` に記述しておきます

{{<code lang="json" title="package.json">}}
{
  "scripts": {
    "start": "concurrently --kill-others \"webpack --watch --watch-poll\" \"electron main.js\""
  },
  ...(略)...
}
{{</code>}}

### 動作確認
プロジェクトの準備が出来たら、WebpackバンドルとElectron実行の動作確認を行いましょう

```bash
# npm scripts: start 実行
## concurrently --kill-others "webpack --watch --watch-poll" "electron main.js"
$ yarn start
```

基本的にElectronの起動の方が早いため、Webpackバンドル完了後 `Ctrl + R` で画面更新する必要があります💦

![webpack-electron.png](/post/nodejs/img/webpack-electron.png)

***

## 見た目を作っていく

### Buefy の導入
見た目を作っていくのにあたって、CSSでスタイルを一から作っていくのは少々面倒です😭

そのため今回は Buefy UIコンポーネントセットを導入する 

- **Buefy**:
    - 軽量CSSフレームワークの **Bulma** をベースにした Vue 用のUIコンポーネントセット
    - Bootstrapのようにあらかじめ用意されたHTMLタグを使うことで簡単にそれなりの見た目を作ることが可能
    - シンプルかつ軽量であり、Webアプリ・Webサイトにも使える汎用性の高いUIコンポーネントであるため採用

```bash
# buefy をローカルインストール
$ yarn add buefy

# Webpack監視・自動バンドル＆Electron実行開始
$ yarn start
```

インストールしたら Buefy を使えるようにします

{{<code lang="diff" title="src/index.js">}}
  import Vue from 'vue'; // Vue を使う
  import App from './App'; // App.vue を読み込む
  
  // IE11/Safari9用のpolyfill
  // babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
  import 'babel-polyfill';
  
+ // Buefy
+ import Buefy from 'buefy';
+ import 'buefy/dist/buefy.css';
+ Vue.use(Buefy);
  
  new Vue({
    el: '#app', // Vueでマウントする要素
    render: h => h(App), // App.vue をレンダリング
  });
{{</code>}}

続いて、アイコンフォント表示用に **Fontawesome** を読み込んでおきます

{{<code lang="diff" title="public/index.html">}}
  <!DOCTYPE html>
  <html lang="ja">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
+     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css">
  </head>
  <body>
      <!-- id: app の要素を Vue で制御 -->
      <div id="app"></div>
      <!-- Webpack でバンドルしたJSファイルを読み込む -->
      <script src="./index.js"></script>
  </body>
  </html>
{{</code>}}

### TODO 画面の実装
Buefy のコンポーネントを使ってパネルとカードUIを実装してみましょう

{{<code lang="html" title="src/App.vue">}}
<template>
  <section class="section">
    <div class="container">
      <h1 class="title">タスク管理アプリ</h1>
      <!-- Collapse Panel -->
      <b-collapse class="panel" :open.sync="isOpen" aria-id="panel_1">
        <div slot="trigger" class="panel-heading notification is-warning" role="button" aria-controls="panel_1">
          <strong>TODO</strong>
        </div>
        <div class="panel-block">
          <!-- Card -->
          <div class="card">
            <header class="card-header">
              <!-- タスクタイトル、タスク編集系ボタン表示 -->
              <span class="card-header-title">TODOアプリ作成</span>
              <button class="card-header-title button is-info is-pulled-right"><i class="fas fa-eye"></i></button>
              <button class="card-header-title button is-link is-pulled-right"><i class="fas fa-edit"></i></button>
              <button class="card-header-title button is-danger is-pulled-right"><i class="fas fa-trash"></i></button>
            </header>
            <footer class="card-footer">
              <!-- 開始日表示 -->
              <time datetime="2020-01-13" class="card-footer-item"><i class="fas fa-hourglass-start"></i>&nbsp;<span>2020/01/13</span></time>
              <!-- 締切日表示 -->
              <time datetime="2020-01-21" class="card-footer-item"><i class="fas fa-hourglass-end"></i>&nbsp;<span>2020/01/21</span></time>
            </footer>
          </div>
        </div>
      </b-collapse>
    </div>
  </section>
</template>
{{</code>}}

ここまで書いたら Electron 上で `Ctrl + R` キーを実行し、画面を更新します

以下のような画面になればOKです

![webpack-electron-buefy.gif](/post/nodejs/img/webpack-electron-buefy.gif)

なお `Ctrl + Shift + I` キーで開発ツール（HTMLエレメントやコンソール等）を表示することもできるため、上手く表示されない場合は活用すると良いです

### コンポーネント分割
`App.vue` に全てのコンポーネントを記述していくと視認性が悪くなり、管理しづらいため、以下のようにコンポーネントを分割することにします

```bash
task-admin/
 |_ src/
 |   |_ components/   # 分割コンポーネント格納ディレクトリ
 |   |   |_ Card.vue  # カードコンポーネント
 |   |   |_ Panel.vue # パネルコンポーネント
 |   |
 |   |_ App.vue
 |   |_ index.js
 :
```

{{<code lang="html" title="src/components/Card.vue">}}
<template>
  <div class="card">
    <header class="card-header">
      <!-- タスクタイトル、タスク編集系ボタン表示 -->
      <span class="card-header-title">{{ task.title }}</span>
      <button class="card-header-title button is-info is-pulled-right"><i class="fas fa-eye"></i></button>
      <button class="card-header-title button is-link is-pulled-right"><i class="fas fa-edit"></i></button>
      <button class="card-header-title button is-danger is-pulled-right"><i class="fas fa-trash"></i></button>
    </header>
    <footer class="card-footer">
      <!-- 開始日表示 -->
      <time :datetime="task.start_date" class="card-footer-item">
        <i class="fas fa-hourglass-start"></i>&nbsp;<span>{{ task.start_date }}</span>
      </time>
      <!-- 締切日表示 -->
      <time :datetime="task.limit_date" class="card-footer-item">
        <i class="fas fa-hourglass-end"></i>&nbsp;<span>{{ task.limit_date }}</span>
      </time>
    </footer>
  </div>
</template>

<script>
/**
 * props として以下のような構造の task オブジェクトを親コンポーネントから受け取る
 * task {
 *   title <string>:      タスク名
 *   start_date <string>: 開始日
 *   limit_date <string>: 締切日
 * }
 */
export default {
  props: ['task'],
}
</script>
{{</code>}}

{{<code lang="html" title="src/components/Panel.vue">}}
<template>
  <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
    <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
      <strong>{{ title }}</strong>
    </div>
    <div class="panel-block">
      <!-- Use Card component as task-card -->
      <!-- Cardコンポーネントの props.task に自身の props.task を渡す -->
      <task-card :task="task" />
    </div>
  </b-collapse>
</template>

<script>
/**
 * props として以下の値を親コンポーネントから受け取る
 * - title <string>:      パネル名
 * - aria_id <string>:    パネル識別ID
 * - class_name <string>: "is-primary"|"is-success"|"is-info"|"is-warning"|"is-danger"|...
 * - task <object>:       @see Card.vue
 */
export default {
  props: ['title', 'aria_id', 'class_name', 'task']
}
</script>
{{</code>}}

{{<code lang="html" title="src/App.vue">}}
<template>
  <section class="section">
    <div class="container">
      <h1 class="title">タスク管理アプリ</h1>
      <!-- Use Panel component as task-panel -->
      <task-panel title="TODO" aria_id="panel_1" class_name="is-warning" :task="task" />
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      // Panelコンポーネントに渡す task データ: @see components/Card.vue
      task: {
        title: 'TODOアプリ作成',
        start_date: '2020/01/13',
        limit_date: '2020/01/21',
      }
    }
  }
}
</script>
{{</code>}}

{{<code lang="diff" title="src/index.js">}}
  import Vue from 'vue'; // Vue を使う
  import App from './App'; // App.vue を読み込む
  
  // IE11/Safari9用のpolyfill
  // babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
  import 'babel-polyfill';
  
  // Buefy
  import Buefy from 'buefy';
  import 'buefy/dist/buefy.css';
  Vue.use(Buefy);
  
+ // Cardコンポーネントを <task-card> として登録する
+ import Card from './components/Card';
+ Vue.component('task-card', Card);
+ 
+ // Panelコンポーネントを <task-panel> として登録する
+ import Panel from './components/Panel';
+ Vue.component('task-panel', Panel);
  
  new Vue({
    el: '#app', // Vueでマウントする要素
    render: h => h(App), // App.vue をレンダリング
  });
{{</code>}}

### VueDraggable の導入
Trello 風のタスク管理アプリを作ろうとするなら、各タスクカードがドラッグ＆ドロップできるようにする必要があります

Vue には **VueDraggable** という便利なプラグインがあるため、今回はこれを使いましょう

```bash
# VueDraggable をローカルインストール
$ yarn add vuedraggable
```

{{<code lang="diff" title="src/index.js">}}
  import Vue from 'vue'; // Vue を使う
  import App from './App'; // App.vue を読み込む
  
  // IE11/Safari9用のpolyfill
  // babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
  import 'babel-polyfill';
  
  // Buefy
  import Buefy from 'buefy';
  import 'buefy/dist/buefy.css';
  Vue.use(Buefy);
  
+ // VueDraggable
+ import VueDraggable from 'vuedraggable';
+ Vue.component('draggable', VueDraggable);
+ 
+ // VueDraggable は値を書き換える系のコンポーネントのため、コンソールに警告が出る
+ // 開発ツールがうるさくなるのが嫌な場合は Vue の警告を無視する
+ Vue.config.warnHandler = (msg, vm, trace) => {
+   msg = null;
+   vm = null;
+   trace = null;
+ }
  
  // ...(略)...
{{</code>}}

{{<code lang="diff" title="src/component/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
+     <!-- VueDraggable -->
+     <draggable v-model="tasks">
+       <div v-for="(task, i) in tasks" :key="aria_id + '_' + i">
+         <!-- Use Card component as task-card -->
+         <!-- ドラッグ可能なことが分かりやすいように cursor: move にしている -->
+         <task-card :task="task" style="cursor: move" />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  <script>
  // ...(略)...
  </script>
{{</code>}}

{{<code lang="diff" title="src/App.vue">}}
  <template>
    <section class="section">
      <div class="container">
        <h1 class="title">タスク管理アプリ</h1>
        <!-- Use Panel component as task-panel -->
+       <task-panel title="TODO" aria_id="panel_1" class_name="is-warning" :tasks="tasks" />
      </div>
    </section>
  </template>
  
  <script>
  export default {
    data() {
      return {
+       // Panelコンポーネントに渡す task データ配列: @see components/Card.vue
+       tasks: [
+         {
+           title: '【実装】TODOアプリ作成',
+           start_date: '2020/01/13',
+           limit_date: '2020/01/21',
+         },
+         {
+           title: '【調査】Node.jsのフローチャート作図系ライブラリ',
+           start_date: '2020/01/14',
+           limit_date: '2020/01/18',
+         },
+         {
+           title: '【読書】あたらしいブロックチェーンの教科書',
+           start_date: '2020/01/17',
+           limit_date: '2020/01/29',
+         },
+       ]
      }
    }
  }
  </script>
{{</code>}}

ここまで実装すると、以下のようにタスクカードをドラッグ＆ドロップできるようになります

![webpack-electron-vuedraggable.gif](/post/nodejs/img/webpack-electron-vuedraggable.gif)

***

## タスクリストの実装

### タスクリストの追加
タスクリストとして「未着手タスク」「実行中タスク」「保留・確認中タスク」「完了タスク」の4つのパネルを実装します

VueDraggable では、同じ `group` 名のリスト間でデータのドラッグ＆ドロップが可能なため、これを利用しましょう

{{<code lang="html" title="src/components/Panel.vue">}}
<template>
  <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
    <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
      <strong>{{ title }}</strong>
    </div>
    <div class="panel-block">
      <!-- VueDraggable -->
      <!--
        - タスク列間でドラッグ＆ドロップできるように group を指定: 同じグループ名の draggable 間でデータのやり取りが可能
        - ドラッグ前と後におけるタスク列のindex情報を data-group に保持
      -->
      <draggable group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]">
        <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i">
          <!-- Use Card component as task-card -->
          <!-- ドラッグ可能なことが分かりやすいように cursor: move にしている -->
          <task-card :task="task" style="cursor: move" />
        </div>
      </draggable>
    </div>
  </b-collapse>
</template>

<script>
/**
 * props として以下の値を親コンポーネントから受け取る
 * - title <string>:      パネル名
 * - aria_id <string>:    パネル識別ID
 * - class_name <string>: "is-primary"|"is-success"|"is-info"|"is-warning"|"is-danger"|...
 * - task_lists <array[array[task]]>:  @see Card.vue
 * - task_list_index <int>:            タスク列の index
 */
export default {
  props: ['title', 'aria_id', 'class_name', 'task_lists', 'task_list_index']
}
</script>
{{</code>}}

{{<code lang="html" title="src/App.vue">}}
<template>
  <section class="section">
    <div class="container">
      <h1 class="title">タスク管理アプリ</h1>
      <div class="columns">
        <!-- 4カラムでパネルを並べる -->
        <div class="column">
          <!-- Use Panel component as task-panel -->
          <task-panel title="未着手" aria_id="panel_1" class_name="is-warning" :task_lists="task_lists" :task_list_index="0" />
        </div>
        <div class="column">
          <task-panel title="実行中" aria_id="panel_2" class_name="is-success" :task_lists="task_lists" :task_list_index="1" />
        </div>
        <div class="column">
          <task-panel title="保留・確認中" aria_id="panel_1" class_name="is-info" :task_lists="task_lists" :task_list_index="2" />
        </div>
        <div class="column">
          <task-panel title="完了" aria_id="panel_1" class_name="is-primary" :task_lists="task_lists" :task_list_index="3" />
        </div>
      </div>
    </div>
  </section>
</template>

<script>
export default {
  data() {
    return {
      // Panelコンポーネントに渡す task データ配列: @see components/Card.vue, components/Panel.vue
      task_lists: [
        // 未着手タスク
        [
          {
            title: '【読書】あたらしいブロックチェーンの教科書',
            start_date: '2020/01/17',
            limit_date: '2020/01/29',
          },
        ],
        // 実行中タスク
        [
          {
            title: '【実装】TODOアプリ作成',
            start_date: '2020/01/13',
            limit_date: '2020/01/21',
          },
        ],
        // 保留・確認中タスク
        [
          {
            title: '【調査】Node.jsのフローチャート作図系ライブラリ',
            start_date: '2020/01/14',
            limit_date: '2020/01/18',
          },
        ],
        // 完了タスク
        [
          {
            title: '【環境構築】Node.js',
            start_date: '2019/12/13',
            limit_date: '2019/12/14',
          },
        ],
      ]
    }
  }
}
</script>
{{</code>}}

![webpack-electron-task_lists.gif](/post/nodejs/img/webpack-electron-task_lists.gif)

### タスクリストが空の場合にドロップできない問題の解決
上記コードを実際に動かしてみると分かりますが、今のままだと、タスクリストが空になってしまったパネルにタスクカードをドロップすることができません

これは、ドロップ先の要素がサイズ0の場合、要素をドロップすることができないためです

そのため、ドロップ領域にはある程度の幅と高さを持たせておく必要があります

今回は、ドロップ領域（パネル内部）をタイル要素とすることで幅と高さを持たせるようにしましょう

Buefy では、親タイルを `.tile .is-parent` で定義し、内部の子タイルを `.tile .is-child` で定義することができます

なお、今回のタイル要素は縦に並んでほしいため、親タイルに `.is-vertical` 属性を付与しています

{{<code lang="diff" title="src/components/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
        <!-- VueDraggable -->
        <!--
          - タスク列間でドラッグ＆ドロップできるように group を指定: 同じグループ名の draggable 間でデータのやり取りが可能
          - ドラッグ前と後におけるタスク列のindex情報を data-group に保持
+         - タスク列が空になってもドロップできるよう、タイル要素にする（class: tile is-parent is-vertical）
        -->
+       <draggable group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]" class="tile is-parent is-vertical">
+         <!-- 各タスクカードは子タイル要素にする（class: tile is-child） -->
+         <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i" class="tile is-child">
            <!-- Use Card component as task-card -->
            <!-- ドラッグ可能なことが分かりやすいように cursor: move にしている -->
            <task-card :task="task" style="cursor: move" />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  
  <script>
  // ...(略)...
  </script>
{{</code>}}

これで実行すると想定通りの挙動になるはずです

![webpack-electron-task_tiles.gif](/post/nodejs/img/webpack-electron-task_tiles.gif)

### Masonryの導入
タスクリストのパネルを横に並べていると、ウィンドウを小さくしたときに全体が表示されず困ることがあります（さらに小さくすると縦に並んでくれますが。。。）

そのため、Masonryを導入して、パネルをタイル状に整列できるようにしてみましょう

- **Masonry**:
    - コンテンツを格子状に自動的に並べてくれる jQuery プラグイン
    - Vue で使えるようにした `vue-masonry` も提供されている
    - サイズの異なる複数のブロックをきれいに整列できるため、ギャラリーサイトなどに最適

```bash
# vue-masonry をローカルインストール
$ yarn add vue-masonry
```

{{<code lang="diff" title="src/index.js">}}
  import Vue from 'vue'; // Vue を使う
  import App from './App'; // App.vue を読み込む
  
  // IE11/Safari9用のpolyfill
  // babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
  import 'babel-polyfill';
  
  // Buefy
  import Buefy from 'buefy';
  import 'buefy/dist/buefy.css';
  Vue.use(Buefy);
  
+ // masonry
+ import {VueMasonryPlugin} from 'vue-masonry';
+ Vue.use(VueMasonryPlugin);
  
  // ...(略)...
{{</code>}}

{{<code lang="diff" title="src/App.vue">}}
  <template>
    <section class="section">
      <div class="container">
        <h1 class="title">タスク管理アプリ</h1>
+       <!--
+         - タスクパネルをMasonryで整列
+         - 整列時のトランジション時間を 0.2 秒とする
+       -->
+       <div v-masonry transition-duration="0.2s">
+         <!--
+           - 各タスクパネルをMasonryタイル（整列対象）とする
+           - 各タイルのスタイルを Scoded CSS で定義: .masonry-tile
+         -->
+         <div v-masonry-tile class="masonry-tile">
            <!-- Use Panel component as task-panel -->
            <task-panel title="未着手" aria_id="panel_1" class_name="is-warning" :task_lists="task_lists" :task_list_index="0" />
          </div>
+         <div v-masonry-tile class="masonry-tile">
            <task-panel title="実行中" aria_id="panel_2" class_name="is-success" :task_lists="task_lists" :task_list_index="1" />
          </div>
+         <div v-masonry-tile class="masonry-tile">
            <task-panel title="保留・確認中" aria_id="panel_1" class_name="is-info" :task_lists="task_lists" :task_list_index="2" />
          </div>
+         <div v-masonry-tile class="masonry-tile">
            <task-panel title="完了" aria_id="panel_1" class_name="is-primary" :task_lists="task_lists" :task_list_index="3" />
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  // ...(略)...
  </script>
  
+ <style scoped>
+ /* Scoped CSS */
+ .masonry-tile {
+   width: 300px;
+   margin: 10px;
+ }
+ </style>
{{</code>}}

***

## タスク管理機能の実装

### タスク追加・削除機能の実装
タスクを追加・削除するボタンを実装します

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
    <div class="card">
      <header class="card-header">
        <!-- タスクタイトル、タスク編集系ボタン表示 -->
        <span class="card-header-title">{{ task.title }}</span>
        <button class="card-header-title button is-info is-pulled-right"><i class="fas fa-eye"></i></button>
        <button class="card-header-title button is-link is-pulled-right"><i class="fas fa-edit"></i></button>
+       <button class="card-header-title button is-danger is-pulled-right" @click.prevent="onRemove"><i class="fas fa-trash"></i></button>
      </header>
      <footer class="card-footer">
        <!-- 開始日表示 -->
        <time :datetime="task.start_date" class="card-footer-item">
          <i class="fas fa-hourglass-start"></i>&nbsp;<span>{{ task.start_date }}</span>
        </time>
        <!-- 締切日表示 -->
        <time :datetime="task.limit_date" class="card-footer-item">
          <i class="fas fa-hourglass-end"></i>&nbsp;<span>{{ task.limit_date }}</span>
        </time>
      </footer>
    </div>
  </template>
  
  <script>
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - task {
   *     title <string>:      タスク名
   *     start_date <string>: 開始日
   *     limit_date <string>: 締切日
   *   }
+  * - onRemove() => task 削除関数
   */
  export default {
+   props: ['task', 'onRemove'],
  }
  </script>
{{</code>}}

{{<code lang="diff" title="src/components/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
        <!-- VueDraggable -->
        <!--
          - タスク列間でドラッグ＆ドロップできるように group を指定: 同じグループ名の draggable 間でデータのやり取りが可能
          - ドラッグ前と後におけるタスク列のindex情報を data-group に保持
          - タスク列が空になってもドロップできるよう、タイル要素にする（class: tile is-parent is-vertical）
        -->
        <draggable
          group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]"
+         @end="$redrawVueMasonry() /* ドロップ時にMasonry再整列実行 */"
          class="tile is-parent is-vertical"
        >
          <!-- 各タスクカードは子タイル要素にする（class: tile is-child） -->
          <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i" class="tile is-child">
            <!-- Use Card component as task-card
              - ドラッグ可能なことが分かりやすいように cursor: move に
+             - onRemove prop に removeTask(i) を実行する関数を渡す
            -->
+           <task-card :task="task" style="cursor: move" :onRemove="() => removeTask(i)" />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  
  <script>
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - title <string>:      パネル名
   * - aria_id <string>:    パネル識別ID
   * - class_name <string>: "is-primary"|"is-success"|"is-info"|"is-warning"|"is-danger"|...
   * - task_lists <array[array[task]]>:  @see Card.vue
   * - task_list_index <int>:            タスク列の index
   */
  export default {
    props: ['title', 'aria_id', 'class_name', 'task_lists', 'task_list_index'],
+   methods: {
+     // タスク削除
+     removeTask(index) {
+       this.task_lists[this.task_list_index].splice(index, 1)
+       // DOM更新後にMasonryによる整列を実行
+       this.$nextTick(function() {
+         this.$redrawVueMasonry()
+       })
+     }
+   }
  }
  </script>
{{</code>}}

{{<code lang="diff" title="src/App.vue">}}
  <template>
    <section class="section">
      <div class="container">
        <h1 class="title">
+         タスク管理アプリ&nbsp;
+         <a class="button is-danger" @click.prevent="addTask"><i class="fas fa-plus-square"></i>&nbsp;タスク追加</a>
        </h1>
        <!--
          - タスクパネルをMasonryで整列
          - 整列時のトランジション時間を 0.2 秒とする
        -->
        <div v-masonry transition-duration="0.2s">
          <!--
            - 各タスクパネルをMasonryタイル（整列対象）とする
            - 各タイルのスタイルを Scoded CSS で定義: .masonry-tile
          -->
          <div v-masonry-tile class="masonry-tile">
            <!-- Use Panel component as task-panel -->
            <task-panel title="未着手" aria_id="panel_1" class_name="is-warning" :task_lists="task_lists" :task_list_index="0" />
          </div>
          <div v-masonry-tile class="masonry-tile">
            <task-panel title="実行中" aria_id="panel_2" class_name="is-success" :task_lists="task_lists" :task_list_index="1" />
          </div>
          <div v-masonry-tile class="masonry-tile">
            <task-panel title="保留・確認中" aria_id="panel_1" class_name="is-info" :task_lists="task_lists" :task_list_index="2" />
          </div>
          <div v-masonry-tile class="masonry-tile">
            <task-panel title="完了" aria_id="panel_1" class_name="is-primary" :task_lists="task_lists" :task_list_index="3" />
          </div>
        </div>
      </div>
    </section>
  </template>
  
  <script>
  export default {
    data() {
      return {
        // Panelコンポーネントに渡す task データ配列: @see components/Card.vue, components/Panel.vue
+       task_lists: [
+         [ /*未着手*/ ], [ /*実行中*/ ], [ /*確認・保留中*/ ], [ /*完了*/ ]
+       ]
+     }
+   },
+   methods: {
+     // 新規タスク追加
+     addTask() {
+       // 未着手タスクリストに新規追加（見やすさのため、タスクリストの先頭に追加）
+       this.task_lists[0].unshift({title: '', start_date: '', limit_date: ''})
+       // DOM更新後にMasonryによる整列を実行
+       this.$nextTick(function() {
+         this.$redrawVueMasonry()
+       })
+     },
+   },
  }
  </script>
  
  <style scoped>
  /* Scoped CSS */
  .masonry-tile {
    width: 300px;
    margin: 10px;
  }
  </style>
{{</code>}}

ここまで実装するとタスクの追加・削除が可能になります

![webpack-electron-masonry.gif](/post/nodejs/img/webpack-electron-masonry.gif)

### 編集ダイアログの実装
続いて、タスクのタイトルや締切日等を編集するダイアログを実装していきます

#### モックアップ
以下のようなダイアログデザインとします

![webpack-electron-editdialog.png](/post/nodejs/img/webpack-electron-editdialog.png)

#### 必要パッケージの導入
編集ダイアログを実装するにあたり、以下のパッケージを導入することにします

- **moment**:
    - JavaScript標準のDateオブジェクトより多機能な日付操作パッケージ
- **vue-ctk-date-time-picker**:
    - カレンダー形式で日付／時刻を入力可能にするVueプラグイン
- **vue-quill-editor**:
    - JavaScript制のWysiwygエディタとして人気の QuillEditor のVueプラグイン
    - TinyMCEなどの有名エディタには及ばないものの、無料で商用利用可能なエディタの中では非常に多機能で使いやすい

```bash
# moment, vue-ctk-date-time-picker, vue-quill-editor をローカルインストール
$ yarn add moment vue-ctk-date-time-picker vue-quill-editor
```

#### 実装
編集ダイアログを実装します

{{<code lang="diff" title="src/index.js">}}
  import Vue from 'vue'; // Vue を使う
  import App from './App'; // App.vue を読み込む
  
  // IE11/Safari9用のpolyfill
  // babel-polyfill を import するだけで IE11/Safari9 に対応した JavaScript にトランスコンパイルされる
  import 'babel-polyfill';
  
  // Buefy
  import Buefy from 'buefy';
  import 'buefy/dist/buefy.css';
  Vue.use(Buefy);
  
  // masonry
  import {VueMasonryPlugin} from 'vue-masonry';
  Vue.use(VueMasonryPlugin);
  
+ // CtkDatetimePicker
+ import VueCtkDateTimePicker from 'vue-ctk-date-time-picker';
+ import 'vue-ctk-date-time-picker/dist/vue-ctk-date-time-picker.css';
+ Vue.component('ctk-datetime-picker', VueCtkDateTimePicker);
+ 
+ // QuillEditor
+ import VueQuillEditor from 'vue-quill-editor';
+ import 'quill/dist/quill.core.css';
+ import 'quill/dist/quill.snow.css';
+ import 'quill/dist/quill.bubble.css';
+ Vue.use(VueQuillEditor);
  
  // ...(略)...
{{</code>}}

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
    <div class="card">
      <header class="card-header">
        <!-- タスクタイトル、タスク編集系ボタン表示 -->
        <span class="card-header-title">{{ task.title }}</span>
        <button class="card-header-title button is-info is-pulled-right"><i class="fas fa-eye"></i></button>
+       <button class="card-header-title button is-link is-pulled-right" @click.prevent="onEdit"><i class="fas fa-edit"></i></button>
        <button class="card-header-title button is-danger is-pulled-right" @click.prevent="onRemove"><i class="fas fa-trash"></i></button>
      </header>
      <footer class="card-footer">
        <!-- 開始日表示 -->
        <time :datetime="task.start_date" class="card-footer-item">
          <i class="fas fa-hourglass-start"></i>&nbsp;<span>{{ task.start_date }}</span>
        </time>
        <!-- 締切日表示 -->
        <time :datetime="task.limit_date" class="card-footer-item">
          <i class="fas fa-hourglass-end"></i>&nbsp;<span>{{ task.limit_date }}</span>
        </time>
      </footer>
    </div>
  </template>
  
  <script>
+ import EditDialog from './EditDialog'
  
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - task {
   *     title <string>:      タスク名
+  *     content <string>:    内容
   *     start_date <string>: 開始日
   *     limit_date <string>: 締切日
   *   }
   * - onRemove() => task 削除関数
   */
  export default {
    props: ['task', 'onRemove'],
+   methods: {
+     // 編集ダイアログ表示
+     onEdit() {
+       this.$buefy.modal.open({
+         parent: this,
+         props: {
+           task: this.task,
+         },
+         component: EditDialog,
+         hasModalCard: true,
+         fullScreen: true,
+         trapFocus: true
+       })
+     }
+   }
  }
  </script>
{{</code>}}

{{<code lang="html" title="src/components/EditDialog.vue (new)">}}
<template>
  <b-form>
    <div class="modal-card" style="width: auto; height: 100%">
      <header class="modal-card-head">
        <p class="modal-card-title">タスク編集</p>
      </header>
      <section class="modal-card-body">
        <b-field label="Title">
          <b-input v-model="title" />
        </b-field>
        <b-field label="開始日">
          <!-- 年月日 時分 を 24時間形式で選択するDatetimePicker -->
          <ctk-datetime-picker v-model="start_date" format="YYYY-MM-DD HH:mm" />
        </b-field>
        <b-field label="締切日">
          <ctk-datetime-picker v-model="limit_date" format="YYYY-MM-DD HH:mm" />
        </b-field>
        <b-field label="Content">
          <quill-editor class="quill-wrap" v-model="content" />
        </b-field>
      </section>
      <footer class="modal-card-footer">
        <b-button @click.prevent="$parent.close()">Close</b-button>
        <b-button class="is-primary" @click.prevent="saveTask">完了</b-button>
      </footer>
    </div>
  </b-form>
</template>

<script>
/**
 * props として以下の値を親コンポーネントから受け取る
 * - task {
 *     title <string>:      タスク名
 *     content <string>:    内容
 *     start_date <string>: 開始日
 *     limit_date <string>: 締切日
 *   }
 */
export default {
  props: ['task'],
  data() {
    return {
      title: '',
      content: '',
      start_date: '',
      limit_date: '',
    }
  },
  
  methods: {
    // 親コンポーネントから渡されたタスクに編集結果を反映
    saveTask() {
      this.task.title = this.title
      this.task.content = this.content
      this.task.start_date = this.start_date
      this.task.limit_date = this.limit_date
      this.$parent.close()
      // DOM更新後にMasonryによる整列を実行
      this.$nextTick(function() {
        this.$redrawVueMasonry()
      })
    }
  },

  // マウント時、親コンポーネントから渡されたタスクの内容をコピー
  mounted() {
    this.title = this.task.title
    this.content = this.task.content
    this.start_date = this.task.start_date
    this.limit_date = this.task.limit_date
  }
}
</script>

<style>
/**
 * QuillEditor のツールバーを固定する
 * => 外部Vueコンポーネント内部の要素に対するスタイル適用のため Scoped CSS は使えない
 */
.ql-container {
  /* Quillエディタ部の最大高さを ViewPort の30％に固定してオーバーフローをスクロールさせる */
  max-height: 30vh;
  overflow: auto;
}
</style>
{{</code>}}

![webpack-electron-edit_task.gif](/post/nodejs/img/webpack-electron-edit_task.gif)

### タスク詳細表示機能の実装
タスクの編集ができるようになったため、タスクの詳細を確認するためのダイアログも実装します

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
    <div class="card">
      <header class="card-header">
        <!-- タスクタイトル、タスク編集系ボタン表示 -->
        <span class="card-header-title">{{ task.title }}</span>
+       <button class="card-header-title button is-info is-pulled-right" @click.prevent="onShow"><i class="fas fa-eye"></i></button>
        <button class="card-header-title button is-link is-pulled-right" @click.prevent="onEdit"><i class="fas fa-edit"></i></button>
        <button class="card-header-title button is-danger is-pulled-right" @click.prevent="onRemove"><i class="fas fa-trash"></i></button>
      </header>
      <!-- 略 -->
    </div>
  </template>
  
  <script>
  import EditDialog from './EditDialog'
+ import DetailDialog from './DetailDialog'
  
  export default {
    props: ['task', 'onRemove'],
    methods: {
      // 編集ダイアログ表示
      onEdit() {
        //...(略)...
      },
+     // 詳細ダイアログ表示
+     onShow() {
+       this.$buefy.modal.open({
+         parent: this,
+         props: {
+           task: this.task,
+         },
+         component: DetailDialog,
+         hasModalCard: true,
+         fullScreen: true,
+         trapFocus: true
+       })
+     }
    }
  }
  </script>
{{</code>}}

{{<code lang="html" title="src/components/DetailDialog.vue (new)">}}
<template>
  <div class="modal-card" style="width: auto; height: 100%">
    <header class="modal-card-head">
      <p class="modal-card-title">{{ task.title }}</p>
    </header>
    <section class="modal-card-body">
      <b-field grouped>
        <!-- 開始日 -->
        <div class="control">
          <b-taglist attached>
              <b-tag type="is-dark"><b-icon pack="fas" icon="hourglass-start" /></b-tag>
              <b-tag type="is-info">{{ task.start_date }}</b-tag>
          </b-taglist>
        </div>
        <!-- 締切日 -->
        <div class="control">
          <b-taglist attached>
            <b-tag type="is-dark"><b-icon pack="fas" icon="hourglass-end" /></b-tag>
            <b-tag type="is-info">{{ task.limit_date }}</b-tag>
          </b-taglist>
        </div>
      </b-field>
      <!-- タスク詳細 -->
      <div class="content" v-html="task.content"></div>
    </section>
    <footer class="modal-card-footer">
      <b-button @click.prevent="$parent.close()">Close</b-button>
    </footer>
  </div>
</template>

<script>
/**
 * props として以下の値を親コンポーネントから受け取る
 * - task {
 *     title <string>:      タスク名
 *     content <string>:    内容
 *     start_date <string>: 開始日
 *     limit_date <string>: 締切日
 *   }
 */
export default {
  props: ['task'],
}
</script>

<style>
/** 
 * QuillEditorのインデントスタイルを適用
 * => 外部Vueコンポーネント内部の要素に対するスタイル適用のため Scoped CSS は使えない
 */
.content .ql-indent-1 {
  margin-left: 1em;
}
.content .ql-indent-2 {
  margin-left: 2em;
}
.content .ql-indent-3 {
  margin-left: 3em;
}
.content .ql-indent-4 {
  margin-left: 4em;
}
.content .ql-indent-5 {
  margin-left: 5em;
}
</style>
{{</code>}}

![webpack-electron-detail.gif](/post/nodejs/img/webpack-electron-detail.gif)

***

## タスク保存機能の実装と仕上げ

### タスク保存機能の実装
今のままではアプリを再起動する度にタスクリストがリセットされてしまいます

そのため、以下のタイミングでタスクリストをファイルに保存するように修正しましょう

- タスクの内容が編集されたとき
- タスクカードが削除されたとき
- タスクカードがドロップされたとき

なお、Electron では Node.js の機能を使うことが可能なため、ローカルファイルの保存・読み込みを行うことができます😍

本稿では、Node.js 機能を使う関数類は `public/api.js` に定義することにします

{{<code lang="diff" title="public/index.html">}}
  <!DOCTYPE html>
  <html lang="ja">
  <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta http-equiv="X-UA-Compatible" content="ie=edge">
      <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.9.0/css/all.min.css">
+     <!-- Node.js の機能を使うAPIを読み込む -->
+     <script src="./api.js"></script>
  </head>
  <body>
      <!-- 略 -->
  </body>
  </html>
{{</code>}}

{{<code lang="javascript" title="public/api.js (new)">}}
const fs = require('fs')

/**
 * 指定パスがファイルか判定 
 */
const isFile = (path) => {
  try {
    return fs.statSync(path).isFile()
  } catch(err) {
    return false
  }
}

/**
 * タスクリスト取得: <= load from ./tasks.json
 */
const loadTasks = () => {
  if (!isFile('./tasks.json')) {
    return [[], [], [], []]
  }
  return JSON.parse(fs.readFileSync('./tasks.json', 'utf8'))
}

/**
 * タスクリスト保存: => save to ./tasks.json
 */
const saveTasks = (tasks) => {
  return fs.writeFileSync('./tasks.json', JSON.stringify(tasks, null, '  '))
}
{{</code>}}

`task_lists` データと `api.js` のラッピングメソッドを mixin でグローバル化します

{{<code lang="diff" title="src/index.js">}}
  // ...(略)...
  
+ /**
+  * public/api.js で定義したAPIのラッピングメソッド
+  * => 同期処理関数（readFileSync, writeFileSync）を使っているため async, await を使って呼び出す
+  * => 全てのコンポーネントから呼び出せるように mixin でグローバルメソッド化
+  */
+ Vue.mixin({
+   data() {
+     return {
+       // Panelコンポーネントに渡す task データ配列: @see components/Card.vue, components/Panel.vue
+       task_lists: [
+         [ /*未着手*/ ], [ /*実行中*/ ], [ /*確認・保留中*/ ], [ /*完了*/ ]
+       ],
+     }
+   },
+   methods: {
+     // タスクリストを読み込み、Masonry再整列実行
+     async loadTasksAndRedrawMasonry() {
+       this.task_lists = await loadTasks()
+       // DOM更新後にMasonryによる整列を実行
+       this.$nextTick(function() {
+         this.$redrawVueMasonry()
+       })
+     },
+ 
+     // タスクリストを保存し、Masonry再整列実行
+     async saveTasksAndRedrawMasonry() {
+       // DOM更新後にMasonryによる整列を実行
+       this.$nextTick(function() {
+         this.$redrawVueMasonry()
+       })
+       await saveTasks(this.task_lists)
+     }
+   }
+ })
  
  new Vue({
    el: '#app', // Vueでマウントする要素
    render: h => h(App), // App.vue をレンダリング
  });
{{</code>}}

`task_lists` は mixin で定義したため `App.vue` から削除します

{{<code lang="diff" title="src/App.vue">}}
  <script>
  export default {
-   data() {
-     return {
-       // Panelコンポーネントに渡す task データ配列: @see components/Card.vue, components/Panel.vue
-       task_lists: [
-         [ /*未着手*/ ], [ /*実行中*/ ], [ /*確認・保留中*/ ], [ /*完了*/ ]
-       ]
-     }
-   },
    // ...(略)...
  }
  </script>
  
  <style scoped>
  /* ...(略)... */
  </style>
{{</code>}}

「タスクカードが削除されたとき」「タスクカードがドロップされたとき」にタスクリストを保存するようにします

{{<code lang="diff" title="src/components/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
        <draggable
          group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]"
+         @end="saveTasksAndRedrawMasonry() /* ドロップ時にMasonry再整列 & タスクリスト保存 実行 */"
          class="tile is-parent is-vertical"
        >
          <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i" class="tile is-child">
            <task-card :task="task" style="cursor: move" :onRemove="() => removeTask(i)" />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  
  <script>
  export default {
    props: ['title', 'aria_id', 'class_name', 'task_lists', 'task_list_index'],
    methods: {
      // タスク削除
      removeTask(index) {
        this.task_lists[this.task_list_index].splice(index, 1)
+       // タスクリスト保存 & Masonry再整列
+       this.saveTasksAndRedrawMasonry()
      },
    },
  }
  </script>
{{</code>}}

続いて「タスクの内容が編集されたとき」の保存処理を追加します。。。

、が！

しかし、ここで問題が発生していることが発覚しました💦

### JavaScript における MutationObserver の無限ループ問題
- 参考: {{<exlink href="https://pisuke-code.com/mutation-observer-infinite-loop/">}}

Vue は DOM 要素を MutationObserver で変更監視して表示更新を行っています

MutationObserver とは以下のような DOM 変化を感知するためのAPIです

- 属性値・CSS値の変化
- 要素の追加・削除
- 要素内のテキストの変更
- その他要素の変化...

通常のイベントでは感知できなかった要素変化を感知できる便利なAPIですが、**変化時に呼ばれるコールバック関数で要素を変更したとき無限ループが発生してしまう**という問題があります

この問題の厄介なところは、MutationObserver が非同期的に処理されるため、無限ループが起こっていても問題が表面化しないことです

今回の場合、`task_lists` 配列を MutationObserver で監視していますが、これの要素（`task`）を `components/Card` => `components/EditDialog` で変更してしまい、以下のような無限ループが発生してしまいます

![webpack-electron-infinite_observer.png](/post/nodejs/img/webpack-electron-infinite_observer.png)

しかし `components/Card` 配下で非同期的無限ループが発生していても、`components/Panel` 以上の親コンポーネント所有の Observer が変更検知するため、表面上は問題なく動作してしまうのです

そのため、この問題を解決しつつ、「タスクの内容が編集されたとき」の保存処理を実装する必要があります

問題解決の方法としてシンプルなものは、`components/Panel::onRemove` メソッドと同じように `components/Panel::onUpdate` のようなメソッドを props として `components/Card` に渡すことです

{{<code lang="diff" title="src/components/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
        <draggable
          group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]"
          @end="saveTasksAndRedrawMasonry() /* ドロップ時にMasonry再整列 & タスクリスト保存 実行 */"
          class="tile is-parent is-vertical"
        >
          <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i" class="tile is-child">
            <!-- Use Card component as task-card
              - ドラッグ可能なことが分かりやすいように cursor: move に
              - onRemove prop に removeTask(i) を実行する関数を渡す
+             - onUpdate prop に updateTask(i, task) を実行する関数を渡す
            -->
+           <task-card
+             :task="task" style="cursor: move" :onRemove="() => removeTask(i)" :onUpdate="(task) => updateTask(i, task)"
+           />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  
  <script>
  export default {
    props: ['title', 'aria_id', 'class_name', 'task_lists', 'task_list_index'],
    methods: {
      // ...(略)...
  
+     // タスク更新
+     updateTask(index, newTask) {
+       this.$set(this.task_lists[this.task_list_index], index, newTask)
+       // タスクリスト保存 & Masonry再整列
+       this.saveTasksAndRedrawMasonry()
+     }
    },
  }
  </script>
{{</code>}}

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
    <!-- (略) -->
  </template>
  
  <script>
  import EditDialog from './EditDialog'
  import DetailDialog from './DetailDialog'
  
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - task {
   *     title <string>:      タスク名
   *     content <string>:    内容
   *     start_date <string>: 開始日
   *     limit_date <string>: 締切日
   *   }
   * - onRemove() => task 削除関数
+  * - onUpdate(task) => task 更新関数
   */
  export default {
+   props: ['task', 'onRemove', 'onUpdate'],
    methods: {
      // 編集ダイアログ表示
      onEdit() {
        this.$buefy.modal.open({
          parent: this,
          props: {
            task: this.task,
+           onUpdate: this.onUpdate,
          },
          component: EditDialog,
          hasModalCard: true,
          fullScreen: true,
          trapFocus: true
        })
      },
      // ...(略)...
  }
  </script>
{{</code>}}

{{<code lang="diff" title="src/components/EditDialog.vue">}}
  <template>
    <!-- (略) -->
  </template>
  
  <script>
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - task {
   *     title <string>:      タスク名
   *     content <string>:    内容
   *     start_date <string>: 開始日
   *     limit_date <string>: 締切日
   *   }
+  * - onUpdate(task) => task 更新関数
   */
  export default {
+   props: ['task', 'onUpdate'],
    data() {
      return {
        title: '',
        content: '',
        start_date: '',
        limit_date: '',
      }
    },
  
    methods: {
      // 親コンポーネントから渡されたタスクに編集結果を反映
      saveTask() {
+       this.onUpdate({
+         title: this.title,
+         content: this.content,
+         start_date: this.start_date,
+         limit_date: this.limit_date,
+       })
        this.$parent.close()
      }
    },
    
    // ...(略)...
  }
  </script>
  
  <style>
  /* ...(略)... */
  </style>
{{</code>}}

これで問題なくタスクリストの保存処理が実現できます

Vue の双方向バインディングは便利ですが、このように MutationObserver の無限ループが起こり得るため十分に注意する必要があります

そういった意味では React のような、親から子への単方向バインディングの設計がシンプルかつ堅牢であるとも言えるかもしれません

### タスク読み込み機能の実装
アプリ起動時に、保存済みのタスクリスト（`tasks.json`）を読み込むように修正します

{{<code lang="diff" title="src/App.vue">}}
  <template>
    <!-- (略) -->
  </template>
  
  <script>
  export default {
    methods: {
      // ...(略)...
    },
  
+   // 起動時にタスクリスト読み込む
+   mounted() {
+     this.loadTasksAndRedrawMasonry()
+   },
  }
  </script>
  
  <style scoped>
  /* ...(略)... */
  </style>
{{</code>}}

### 日付表示の修正
タスクカードの開始日および締切日を `年/月/日 時:分` 形式で表示します

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
    <div class="card">
      <header class="card-header">
        <!-- (略) -->
      </header>
      <footer class="card-footer">
        <!-- 開始日表示 -->
        <time :datetime="task.start_date" class="card-footer-item">
+         <i class="fas fa-hourglass-start"></i>&nbsp;<span>{{ displayDatetime(task.start_date) }}</span>
        </time>
        <!-- 締切日表示 -->
        <time  :datetime="task.limit_date" class="card-footer-item">
+         <i class="fas fa-hourglass-end"></i>&nbsp;<span>{{ displayDatetime(task.limit_date) }}</span>
        </time>
      </footer>
    </div>
  </template>
  
  <script>
+ import moment from 'moment'
  import EditDialog from './EditDialog'
  import DetailDialog from './DetailDialog'
  
  export default {
    props: ['task', 'onRemove', 'onUpdate'],
    methods: {
      // ...(略)...
  
+     // 日付データを表示
+     displayDatetime(date) {
+       return date? moment(date).format('YYYY/MM/DD HH:mm'): ''
+     }
    },
  }
  </script>
{{</code>}}

### 完了日の表示
完了タスクリストは、締切日の代わりに完了日を表示するように変更します

{{<code lang="diff" title="src/components/Panel.vue">}}
  <template>
    <b-collapse class="panel" :open.sync="isOpen" :aria-id="aria_id">
      <div slot="trigger" :class="'panel-heading notification ' + class_name" role="button" :aria-controls="aria_id">
        <strong>{{ title }}</strong>
      </div>
      <div class="panel-block">
        <draggable
          group="task_admin" :data-group="task_list_index" v-model="task_lists[task_list_index]"
+         class="tile is-parent is-vertical" @end="onDrop"
        >
          <div v-for="(task, i) in task_lists[task_list_index]" :key="aria_id + '_' + i" class="tile is-child">
            <task-card
              :task="task" style="cursor: move" :onRemove="() => removeTask(i)" :onUpdate="(task) => updateTask(i, task)"
            />
          </div>
        </draggable>
      </div>
    </b-collapse>
  </template>
  
  <script>
+ import moment from 'moment'

  export default {
    props: ['title', 'aria_id', 'class_name', 'task_lists', 'task_list_index'],
    methods: {
      // ...(略)...
  
+     // タスクカードDrop時イベント
+     onDrop(evt) {
+       // 完了タスク以外から完了タスクに移動したら、完了日（＝現在日時）セット
+       const completed_index = 3
+       if (evt.from.dataset.group != completed_index && evt.to.dataset.group == completed_index) {
+         this.$set(this.task_lists[completed_index][evt.newIndex], 'end_date', moment().format('YYYY-MM-DD HH:mm'))
+       }
+       // 完了タスクから完了タスク以外に移動したら、完了日を削除
+       if (evt.from.dataset.group == completed_index && evt.to.dataset.group != completed_index) {
+         this.$set(this.task_lists[evt.to.dataset.group][evt.newIndex], 'end_date', null)
+       }
+       // ドロップ時にMasonry再整列 & タスクリスト保存 実行
+       this.saveTasksAndRedrawMasonry()
+     }
    },
  }
  </script>
{{</code>}}

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
+   <div :class="'card ' + (task.end_date? 'is-completed': '')">
      <header class="card-header">
        <!-- タスクタイトル、タスク編集系ボタン表示 -->
        <span class="card-header-title">{{ task.title }}</span>
        <button class="card-header-title button is-info is-pulled-right" @click.prevent="onShow"><i class="fas fa-eye"></i></button>
        <button class="card-header-title button is-link is-pulled-right" @click.prevent="onEdit"><i class="fas fa-edit"></i></button>
        <button class="card-header-title button is-danger is-pulled-right" @click.prevent="onRemove"><i class="fas fa-trash"></i></button>
      </header>
      <footer class="card-footer">
        <!-- 開始日表示 -->
        <time :datetime="task.start_date" class="card-footer-item">
          <i class="fas fa-hourglass-start"></i>&nbsp;<span>{{ displayDatetime(task.start_date) }}</span>
        </time>
+       <!-- 完了タスク以外は締切日を表示 -->
+       <time v-if="!task.end_date" :datetime="task.limit_date" class="card-footer-item">
+         <i class="fas fa-hourglass-end"></i>&nbsp;<span>{{ displayDatetime(task.limit_date) }}</span>
+       </time>
+       <!-- 完了タスクは完了日を表示 -->
+       <time v-else :datetime="task.end_date" class="card-footer-item ">
+         <i class="fas fa-check"></i>&nbsp;<span>{{ displayDatetime(task.end_date) }}</span>
+       </time>
      </footer>
    </div>
  </template>
  
  <script>
  import moment from 'moment'
  import EditDialog from './EditDialog'
  import DetailDialog from './DetailDialog'
  
  /**
   * props として以下の値を親コンポーネントから受け取る
   * - task {
   *     title <string>:      タスク名
   *     content <string>:    内容
   *     start_date <string>: 開始日
   *     limit_date <string>: 締切日
+  *     end_date <string>:   完了日
   *   }
   * - onRemove() => task 削除関数
   * - onUpdate(task) => task 更新関数
   */
  export default {
    // ...(略)...
  }
  </script>
  
+ <style scoped>
+ /* Scoped CSS */
+ .is-completed {
+   /* 完了タスクカードはグレーにする */
+   color: #444;
+   background-color: #aaa;
+ }
+ </style>
{{</code>}}

![webpack-electron-complete.gif](/post/nodejs/img/webpack-electron-complete.gif)


### 最終調整: 締め切り間近・期限切れタスクカードの色変更
最終調整として、締め切り間近・期限切れタスクカードの色を変更し、目立つようにさせましょう

{{<code lang="diff" title="src/components/Card.vue">}}
  <template>
+   <div :class="'card ' + getTaskClass()">
      <!-- (略) -->
    </div>
  </template>
  
  <script>
  import moment from 'moment'
  import EditDialog from './EditDialog'
  import DetailDialog from './DetailDialog'
  
  export default {
    props: ['task', 'onRemove', 'onUpdate'],
    methods: {
      // ...(略)...
  
+     // タスクカードの class を取得: 状態による色変更
+     getTaskClass() {
+       if (this.task.end_date) {
+         // 完了タスク
+         return 'is-completed'
+       }
+       // 現在日時と締切日時の差分から文字色変更
+       if (!this.task.limit_date) {
+         return ''
+       }
+       const diff = moment(this.task.limit_date).diff(moment(), 'minutes')
+       if (diff < 0) {
+         // 締切日超過
+         return 'is-out'
+       }
+       if (diff <= 60 * 24) {
+         // 本日締切
+         return 'is-limit'
+       }
+       return ''
+     }
    },
  }
  </script>
  
+ <style scoped lang="scss">
+ /* Scoped SCSS */
+ 
+ .card:hover {
+   /* タスクカードを浮かせる */
+   box-shadow: 0 3px 6px 0 rgba(0, 0, 0, 0.25);
+   transform: translateY(-0.1875em);
+ }
+ 
+ .is-completed {
+   /* 完了タスク */
+   color: #444;
+   background-color: #aaa;
+   .card-header-title {
+     color: #444;
+     font-weight: bold;
+     &.button {
+       color: white;
+     }
+   }
+ }
+ 
+ .is-limit {
+   /* 本日締め切り */
+   color: #0b6;
+   background-color: #ffd;
+   .card-header-title {
+     color: #0b6;
+     font-weight: bold;
+     &.button {
+       color: white;
+     }
+   }
+ }
+ 
+ .is-out {
+   /* 期限切れ */
+   color: #e40;
+   background-color: #fdf;
+   .card-header-title {
+     color: #e40;
+     font-weight: bold;
+     &.button {
+       color: white;
+     }
+   }
+ }
+ </style>
{{</code>}}

これで完成！🎉

![webpack-electron-complete2.gif](/post/nodejs/img/webpack-electron-complete2.gif)
