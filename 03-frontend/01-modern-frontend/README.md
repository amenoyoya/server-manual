# React とか Vue とか Webpack とかで混乱している人のための現代フロントエンド開発入門

## Webフロントエンド開発の歴史

1991年に Web (World Wide Web) がインターネット上に誕生した当初、Web は文書閲覧のためのものだった

そのため、現在我々が利用するような SNS や Google Maps のようなインタラクティブコンテンツ（ユーザと Web が対話的にやりとりするようなコンテンツ）は存在せず、ユーザは Web が配信する文書を一方的に受信するだけだった

![web01.drawio.png](./img/web01.drawio.png)

1990年代半ば以降、CSS や JavaScript がWebブラウザに搭載されるようになり、よりリッチなコンテンツの配信が可能となってきた

しかしながら、当時の JavaScript はまだ貧弱で、CSS ともに文書の装飾に用いられることが一般的だった

同時期、CGI を始めとする Web 向けのサーバサイドプログラム技術が登場した

これにより、データベースサーバによるデータ管理／サーバサイドプログラムによる動的なHTML文書の生成／Webクライアント（ブラウザ）という古典的なWebシステムが生まれた

この、サーバ側がデータベースやアプリケーション本体を担い、クライアントは見た目を担うという形式は形を変えつつ現在でもある程度支持されている

![web02.drawio.png](./img/web02.drawio.png)

その後もサーバサイド側は CGI から進化を続け、Ruby on Rails のような MVC (Model View Controller) ベースのフレームワークが登場することで、ECサイトやブログのような複雑なWebシステムが作られるようになってきた

一方、フロントエンド側はそれほど大きく進化することなく、CSS による装飾、JavaScript によるアラートや入力受付程度の簡単な処理のみを求められていた

### Ajax の登場
前述の通り、しばらくの間フロントエンドは見た目の補助以上の処理を求められなかったが、2005年にGoogle社がリリースした Google Maps によりこの常識は覆された

Google Maps は地図サービスであるが、当時としては革新的な、ページを遷移することなくWebブラウザ側で地図を拡大・縮小する機能を提供した

これによりユーザは、地図を拡大・縮小する際に毎回サーバからの応答を待つことなくシームレスに操作を行うことが可能になったのである

これは **Ajax** と呼ばれる、JavaScript によってサーバと非同期に通信する技術によって実現されている

Ajax の登場により、Webブラウザでもデスクトップアプリケーションのようなインタラクティブなアプリケーション開発が可能となり、フロントエンドはより高度な処理を求められるようになってきた

例えば、Ajax 通信の結果により DOM (HTMLタグによって構築されている木構造) の構造を変化させるような処理である

![ajax.drawio.png](./img/ajax.drawio.png)

なお上図の【Ajax通信を使ったインタラクティブ処理】において、「サーバからは必要な情報のみ返す」とあるが、このような仕様を **Web API** と呼ぶ

この頃、高度な DOM 操作を簡単に実現できるライブラリとして **jQuery** が人気を集めた

最近では React.js や Vue.js といった仮想DOM操作ライブラリによって取って代わられつつあるが、それでも jQuery を使ったフロントエンド開発は未だに根強く残っている

ともあれ、こうして Ajax と Web API により、よりリッチなWeb業務システムなWebサービスを構築することが一般的となり、Webクライアント（ブラウザ）側でも JavaScript を駆使して本格的なプログラムが書かれるようになった

この頃から、開発体制としてサーバサイドとクライアントサイドの分業化が見られるようになり、バックエンドエンジニアとフロントエンドエンジニアという職種が現れた

### HTML5の登場とフロントエンド開発の高度化
2000～2010年代にかけて Web はさらに複雑化・高度化していった

そして2014年に HTML の新規格として **HTML5** が登場し、Web 全体の仕様をアップデートする大きなムーブメントが発生した

現在の HTML は **HTML Living Standard** という規格に統一され、頻繁に仕様追加・変更が行われており、フロントエンド技術は加速度的に進化している

こうして、HTML5 の登場とそれに伴う CSS 仕様、JavaScript ライブラリの進化などで、クライアントサイドにおいてもより強⼒な表現が可能となった

これを受けて、プレゼンテーション層のプログラムがサーバーサイドからクライアントサイドにシフトしていき、従来サーバーサイドで⾏なっていた HTML の描画がクラ
イアントサイドで行われるようになってきた

これは、クライアントサイドで HTML 描画を行うことで画⾯遷移を減らし、ユーザにより優れたWeb体験を与えられるためである

### Node.js による JavaScript エコシステムの進化
2009年、サーバサイド JavaScript 環境である **Node.js** が登場した

これは元々はサーバサイド側で JavaScript 言語を扱うことができるというだけのものだが、フロントエンド開発にも以下の2つの大きな変化を与えた

- フロントエンド開発・検証の効率化:
    - 本来 JavaScript はブラウザ内で動くため、PC内のファイルを読み込んだり書き込んだりすることはできない
    - しかし、Node.js によりブラウザ外で JavaScript を実行可能となり、コーディング中のファイル監視をして、ファイル変更に合わせて自動的にブラウザをリロードしたりするようなことが可能となった
    - また、記述した JavaScript コードのテスト等も簡単に行えるようになり、フロントエンド開発・検証が効率的にできるようになった
- パッケージマネージャ npm の普及:
    - JavaScript で実装されたライブラリを npm 経由で利用できるようになったことで、開発したものをモジュール化して配布する文化が育った
    - これによりコード資産が共有できるようになり、開発効率が格段に向上した
    - このように、複数の開発者が互いの技術や資本を生かしながら広く共存共栄していく仕組みを **エコシステム** と呼ぶ

### ES2015 によるプログラミング言語としての JavaScript の進化
Webフロントエンド開発が⾼度化するなかで問題になったのが JavaScript の⾔語機能の貧弱さだった

元々ブラウザでちょっとした飾り付け等を行うことを目的として開発された JavaScript は、本格的なアプリケーションを作成する上では物⾜りないところが多かったのである

そこで⼤々的な仕様のアップデートが求められ、登場したのが **ES2015** (ECMAScript 2015) という規格である

ES2015 は JavaScript の歴史上でも最⼤のアップデートであり、構⽂が増え、`const` や `let` 宣言の普及など、書き⽅も⼤々的に変わることになった

しかしながら、サーバサイドで動く Node.js などは仕様のアップデートに合わせてバージョンアップすれば済むが、クライアントサイド（ブラウザ）で動く JavaScript は、仕様が提案されてすぐに全てのブラウザに実装されるわけではなく、統一的に書くことが難しい

しかし、多くの仕様変更は古い JavaScript への不満を解消する魅⼒的なものである

そこで、こういった仕様をブラウザ実装に先駆けて利⽤しようとする動きが広がってきた

**Babel** はこういったニーズに応える **JavaScript to JavaScript Compiler** である

すなわち、次世代の JavaScript を、まだその仕様を実装していないブラウザで動作する JavaScript に変換するという訳である

なお、Babel は基本的に Node.js で動くコンパイラであるため、Babel を使いたい場合は Node.js の導入も必須となる

こうして ES2015 以降の仕様の⼈気で、本来はコンパイルという過程を必要としない JavaScript 開発において、JavaScript to JavaScript Compile というビルド手順が増えてしまった

このような状況が、最近のフロントエンド開発への入門の敷居を上げているのではないかと感じられる

### Webpack について
**Webpack** はモジュールバンドラの一つで、複数の JavaScript ファイルを1ファイルにパッキングするものである

元々 JavaScript には、別のファイルに記述された JavaScript モジュールを読み込むという機能がないため、それを実現するモジュールバンドラの登場で大規模な JavaScript 開発が可能となった

特に npm パッケージマネージャを中心としたエコシステムを十分活用するためには、それぞれ分かれて開発されている各モジュールを上手く一つにまとめる機能は必須となる

そのため、フロントエンド開発において Node.js 環境を導入する場合、Webpack の使用もほぼ必須となってくる

Webpack と Babel は一緒に使われることが多いため、ごっちゃになってしまっている人も多そうだが、あくまで用途は別のものである

なお、最近のブラウザ内 JavaScript では、[ES Module](https://developer.mozilla.org/ja/docs/Web/JavaScript/Reference/Statements/import) という仕様が採用されており、モジュール読み込み機能が使えるようになりつつある

そのため、これから先のフロントエンド開発では Webpack は必ずしも必要なくなり、ES Module 機能を効率的に扱うための [Snowpack](https://www.snowpack.dev/) のような開発環境が主流になっていく可能性が高い

### React, Vue 等のフロントエンドライブラリの出現
ここまで紹介してきたようにフロントエンドを取り巻く仕様、技術は⾼度化している

これらが可能になったことで、アプリケーション、サービスにおいても複雑な要件が求められるようになった

すなわち、アプリケーションデータフローをフロントエンド側で受け持つなど、設計段階から難易度が上がってきたのである

DOM を Web API と連携させて適切に書き換えるのも考えなしにはできない

こうなってくるとアプリケーションの構造化を持たない jQuery のようなライブラリでは⼒不⾜である

そのため、MVC のようなアプリケーションの構造を持ったフレームワークが必要とされるようになり、Backbone.js, AngularJS などの新たなWebアプリケーションフレームワーク、ライブラリが次々と出現した

この流れの中で現れたのが、Facebook社が開発した **React** と **Flux** である（React はビューライブラリ、Flux はアプリケーションアーキテクチャ）

React を中⼼とした開発スタイルは仮想DOMによってDOM操作を⾼速で快適なものにし、また、Flux によって混乱しがちなフロントエンドのアーキテクチャに⽅向性が⽰され、React／Flux は大人気のライブラリとなった

React などの登場によって、⾼度なフロントエンドアプリケーションの開発はjQueryで無理やりにつくるよりも構造化しやすくなった

ここで新しく登場するのが、学習コストの問題である

React 自身もAPIを⼩さく保つなど学習コストをむやみに増やさない設計をしているが、JSX、データフローに関する知識など、React 導⼊に当たって少なくない知識が必要とされるのもまた事実である

このような学習コストの高さを低減するべく開発されたのが **Vue** というライブラリである

Vue はシンプルなAPIを提供し、UIの構築にはHTMLベースの平易なテンプレートを利⽤できる

そのため、HTML や JavaScript を多少触っていれば、Vue.js 固有の知識がほとんどなくてもすぐに利⽤可能となっている

一方、React のように固い設計はしづらいというデメリットもあり、大規模なプロジェクトでは React が採用されることが多い印象である

ともあれ、最近のフロントエンド開発では React や Vue といった構造的なアプリケーション開発可能なライブラリを採用することが多くなっているということである

***

## Node.js を使わない Vue, React 入門

前述の通り、Node.js を使うことでフロントエンド開発・検証を効率的に行うことができる

また、Node.js で動く Babel という JavaScript to JavaScript Compiler を使えば、ブラウザごとの JavaScript 仕様の違いを気にせず、最新の JavaScript 仕様（ES2015 以降の仕様）を使ってプログラムを書くこともできる

しかしながら、これらはあくまで開発効率を向上させるための手段であり、Vue や React のような最新ライブラリを使うのに必須なわけではない

最終的に各ブラウザに搭載された JavaScript エンジンが、JavaScript ファイルを解釈して実行するという基本原理は今も昔も変わらないためである

そのためここでは、まず Node.js を開発に使わない古典的な手法で Vue, React ライブラリを使ったフロントエンド開発を体験してみる

### Environment
- Terminal:
    - Bash (Ubuntu 20.04)
    - Zsh (macOS)
    - PowerShell (Windows 10)
- Editor: VSCode
    - Live Server Extension
- Browser: Google Chome

ここでは、VSCode をエディタとして採用し、[Live Server](https://marketplace.visualstudio.com/items?itemName=ritwickdey.LiveServer) 拡張機能を使うことにする

無論これは必須ではないが、ファイルの変更を検知して自動的にブラウザのページをリロードしてくれたりして便利なため採用している

詳細は [VSCodeエディタを使ったフロントエンド開発](../README.md#VSCodeエディタを使ったフロントエンド開発) を参照

また、ブラウザは極力 Google Chrome を使うことを推奨する

前述の通り、ブラウザごとに JavaScript で使用可能な文法等の仕様が異なり、以降のコードが必ずしもすべてのブラウザで問題なく実行されるか未検証のためである

### Node.js を使わない Vue 入門
ターミナル（Bash, Zsh, PowerShell 等）から以下の通りプロジェクトディレクトリの作成とVSCodeの起動を行う

```bash
# プロジェクトディレクトリ `simple-vue` 作成
$ mkdir simple-vue

# simple-vue ディレクトリで VSCode 起動
$ cd simple-vue/
$ code .
```

VSCode を開いたら、以下のようにファイルを作成する

```bash
simple-vue/
|_ index.html # トップページ
|_ main.js    # index.html から読み込まれる JS ファイル
```

![project.png](./img/project.png)

#### index.html
```html
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Node.js を使わない Vue 入門</title>

  <!-- 簡単に見栄えを良くするため Bootstrap 5 CSS を CDN 経由でロード -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">

  <!-- Vue 3.1.5 を CDN 経由でロード -->
  <script src="https://unpkg.com/vue@3.1.5"></script>
</head>
<body>
  <div id="app" class="container my-4">
    <h1 class="title">Node.js を使わない Vue 入門</h1>
    <div class="card">
      <div class="card-body">
        <h2 class="card-title">Vue によるカウンター</h2>
        <p class="card-text">
          <!-- {{ 変数 }}: Vue により DOM 操作され、変数が埋め込まれる -->
          Count: <span class="text-light bg-dark px-2 py-1">{{ count }}</span>
        </p>
        <div class="d-flex justify-content-end">
          <!-- @click.prevent: ボタンのデフォルトクリックイベントを消し、Vue 側で定義した countUp メソッドを実行する -->
          <button class="btn btn-primary me-5" @click.prevent="countUp">カウントアップ</button>
        </div>
      </div>
    </div>
  </div>

  <!--
    このページ用のメイン JS スクリプトをロード
    ※ Vue を使う場合は、必ず body タグの最後でスクリプトを実行すること
  -->
  <script src="./main.js"></script>
</body>
</html>
```

上記のように、Vue では HTML に特殊な記法を用いることができ、`{{ 変数名 }}` という書き方で Vue 側の変数を埋め込むことができる

また、`@イベント名` (正式には `v-on:イベント名`) という属性を指定することも可能で、これにより Vue 側で定義したイベントメソッドを実行させることができる

他にも様々な HTML 拡張記法があるため [公式ガイド](https://v3.ja.vuejs.org/guide/introduction.html) を確認すると良い

#### main.js
```javascript
/**
 * 本スクリプトには ES2015 以降の文法が含まれる（オブジェクトイニシャライザの短縮メソッド定義等）
 * モダンなブラウザでは基本的にサポートされているはずだが、動かない場合は最新の Google Chrome で確認すること
 */

// Vue Application 定義
const VueApp = {
  // Vue 変数宣言: ここで返したオブジェクトマップが変数として利用可能
  data() {
    return {
      // {{ count }} は初期状態で 0 として表示される
      count: 0,
    }
  },

  // メソッド宣言: ここで定義したメソッドを v-on:*** (もしくは @***) イベントで呼び出すことができる
  methods: {
    // countUp メソッド: Vue 変数 `count` をインクリメントする
    countUp() {
      this.count++;
    }
  }
};

// id="app" のエレメントをマウントし、Vue 側で HTML を構築できるようにする
Vue.createApp(VueApp).mount('#app');
```

上記のように、変数やメソッドを定義したオブジェクトを `Vue.createApp` に渡し、それを HTML エレメントにマウントすることで Vue で仮想DOMを操作できるようになる

なお、ここでは HTML と JavaScript を別ファイルに分けているが、コンポーネント単位で HTML／CSS／JavaScript を一つのファイルにまとめて記述する機能もある（**Single File Component (SFC)**）

しかし、SFC機能は基本的に Node.js 環境を構築して、SFC（`.vue` ファイル）をブラウザで実行可能な JavaScript ファイルに変換して使う形になる

#### 動作確認
VSCode 右下の「Go Live」アイコンをクリックするか、`F1` キー > `Live Server: Open with Live Server` コマンドを実行すると、ブラウザが起動してページを確認できるはずである（なお `Alt` + `L` |> `Alt` + `O` キーでも Live Server を起動できる）

以下のようにカウンターアプリケーションが実行されればOK

![simple-vue.gif](./img/simple-vue.gif)

### Node.js を使わない React 入門
ターミナル（Bash, Zsh, PowerShell 等）から以下の通りプロジェクトディレクトリの作成とVSCodeの起動を行う

```bash
# プロジェクトディレクトリ `simple-react` 作成
$ mkdir simple-react

# simple-react ディレクトリで VSCode 起動
$ cd simple-react/
$ code .
```

VSCode を開いたら、以下のようにファイルを作成する

```bash
simple-react/
|_ index.html # トップページ
|_ main.jsx   # index.html から読み込まれる JSX ファイル
```

JSX という見慣れないファイルがあるが、これは **HTMLタグ（風のタグ）を直接 JavaSCript コードの中で書けるように拡張された JavaScript 拡張言語** である

これについて、詳しくは後述する

#### index.html
```html
<!DOCTYPE html>
<html lang="ja">
<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Node.js を使わない React 入門</title>

  <!-- 簡単に見栄えを良くするため Bootstrap 5 CSS を CDN 経由でロード -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta1/dist/css/bootstrap.min.css">

  <!-- React 16 を CDN 経由でロード -->
  <script src="https://unpkg.com/react@16/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@16/umd/react-dom.development.js"></script>

  <!-- React JSX スクリプトを解釈できるようにするための Babel ライブラリをロード -->
  <script src="https://unpkg.com/babel-standalone@6/babel.min.js"></script>
</head>
<body>
  <div id="app" class="container my-4"><!-- この中の HTML は React 側で構築 --></div>

  <!--
    このページ用のメイン JSX スクリプトをロード
    ※ React を使うには JSX という JavaScript 拡張言語を使う必要がある
    ※ JSX を使うには Bebel ライブラリをロードして script type="text/babel" でスクリプトを記述する
  -->
  <script type="text/babel" src="./main.jsx"></script>
</body>
</html>
```

JSX は JavaScript の拡張言語であるため、そのままではブラウザで実行することはできない

こういった場合、通常は Node.js + Babel を使って JSX to JavaScript Compile を行い、JSX をブラウザで実行可能な JavaScript ファイルに変換する必要がある（実は Bebel は ES2015 以降の文法だけでなく JSX 構文も JavaScript に変換できるということである）

そのため React でのフロントエンド開発には、基本的に Node.js の導入が不可欠なのだが、実はピュアJS（ブラウザで実行可能な JavaScript）で書かれた Bebel Compiler も配布されており、これを利用することで Node.js なしに JSX ファイルを直接ブラウザで実行することが可能である

動作原理自体はシンプルで、ピュアJS製の Babel は、HTML 内にある `<script type="text/babel">` というタグを検索し、その中に記述された JSX コードを JavaScript にコンパイルした上で実行している

すなわち、本来は事前に JSX to JavaScript Compile された JavaScript ファイルを読み込んで実行するところを、ブラウザ上で JSX to JavaScript Compile して、そのコンパイル結果の JavaScript を実行しているということである

ブラウザにコンパイルを任せてしまう分だけ、動作速度は遅くなってしまうため注意が必要である

#### main.jsx
```jsx
/**
 * App component
 * @description React では JSX という独自言語で HTML タグをそのまま使うことができる
 * @returns {JSX}
 */
function App() {
  // カウンタ変数: 初期値 0
  let [count, setCount] = React.useState(0);

  // カウンタ変数をインクリメントする関数: カウントアップ button の onClick イベントとして埋め込まれる
  function countUp(e) {
    e.preventDefault(); // ボタンクリックのデフォルトイベントを削除
    
    // React で変数の値を更新するためには React.useState の第2戻り値の関数を使う必要がある
    setCount(count + 1);
  }

  /**
   * JSX の書き方:
   * - JSX では複数の HTML タグを同じ階層に配置して扱うことはできない
   *   - そのため複数のタグを扱いたい場合は、適当なタグの中に入れて一つのタグにまとめる必要がある
   * - class の代わりに className という属性を使う必要がある
   * - 変数や関数は { 変数（関数）名 } という形で埋め込む
   */
  return (
    <div>
      <h1 className="title">Node.js を使わない React 入門</h1>
      <div className="card">
        <div className="card-body">
          <h2 className="card-title">React によるカウンター</h2>
          <p className="card-text"> 
            Count: <span className="text-light bg-dark px-2 py-1">{ count }</span>
          </p>
          <div className="d-flex justify-content-end">
            <button className="btn btn-primary me-5" onClick={ countUp }>カウントアップ</button>
          </div>
        </div>
      </div>
    </div>
  );
}

/**
 * React JSX で構築した HTML を id="app" のエレメントの中に描画
 * @description React JSX を返す関数は <関数名 /> という独自タグとして呼び出すことができる
 */
ReactDOM.render(<App />, document.getElementById('app'));
```

上記のように JSX では、普通の JavaScript 関数の戻り値として HTML タグ風のタグをそのまま記述するようなことが可能である

このような記述が Babel によりピュアJSに変換されるわけだが、実際どのように変換されるのかは理解しておいたほうが良い

例えば以下のような JSX コードがあるとして、

```jsx
function Hello() {
  return <p>Hello</p>;
}
```

これは以下のような JavaScript に変換される

```javascript
function Hello() {
    return React.createElement('p', null, 'Hello');
}
```

つまり、JSX タグは最終的に `React.createElement` により仮想DOM要素として生成されることになる

この辺りの話はやや難しいため、実際 React をプロジェクトで採用する際に改めて勉強すると良い

#### 動作確認
Live Server を起動して、以下のようにカウンターアプリケーションが実行されればOKである

![simple-react.gif](./img/simple-react.gif)

***

## Node.js 入門

前述の通り、Vue も React もただ使うだけであれば、Node.js を開発環境に導入する必要はない

しかしながら、Vue SFC や React JSX 等、事前にピュアJSにコンパイルしておいた方が、ブラウザ上でいちいちコンパイルしなくて良くなるためパフォーマンス的に有利である

そこでここでは Node.js 環境を構築し、その基本的な使い方を習得する

### Setup
[WSL開発環境構築](../../WSL開発環境構築.md) で環境構築してある場合は、anyenv + nodenv を使って Node.js 環境済みのはずである

上記で Linux (Ubuntu 20.04) での環境構築方法は紹介済みのため、ここでは macOS (11 Big Sur) と Windows 10 (WSL2 を使わない場合) での環境構築方法を掲載する

なお、環境構築時に Node.js のサードパーティ製パッケージマネージャとして `yarn` を導入しているが、これはかつての `npm` パッケージマネージャがインストール速度やセキュリティの問題を抱えていたためである

現在では **`npm` 側に `yarn` の多くの機能が取り込まれ、実質的な機能の差はほとんどなくなっている**ため、必ずしも `yarn` を導入する必要はないと考えている

#### Setup on macOS 11 Big Sur
基本的には Linux と大きく変わらない

`Command` + `Space` |> `terminal.app`

（以下、デフォルトシェルが `zsh` である前提で進める）

```bash
# --- anyenv 導入 ---

# Homebrew 未導入の場合は導入しておく
## 最近のインストーラは自動的に Xcode Command Line Tools も入れてくれるため、一通りの開発環境は簡単に整う
$ /bin/bash -c "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/install.sh)"

# Linuxbrew で anyenv 導入
$ brew install anyenv
$ anyenv install --init
## Do you want to checkout ? [y/N]: <= y

# anyenv 初期化スクリプトを .zshrc に記述
$ echo 'eval "$(anyenv init -)"' >> ~/.zshrc
$ source ~/.zshrc

# anyenv update plugin の導入
$ mkdir -p $(anyenv root)/plugins
$ git clone https://github.com/znz/anyenv-update.git $(anyenv root)/plugins/anyenv-update
$ anyenv update

# バージョン確認
$ anyenv -v
anyenv 1.1.4


# --- pyenv 導入 ---
## npm package の中には python を必要とするものも多いため、ここで Python 環境を導入しておく

# anyenv を使って pyenv 導入
## pyenv を使うことで、複数バージョンの Python 環境を構築できる
$ anyenv install pyenv
$ exec $SHELL -l

# pyenv で Python 2.7.18 と 3.7.7 をインストール
$ pyenv install 2.7.18
$ pyenv install 3.7.7

# pyenv では 2系 と 3系 を同時に指定できる
## python  => 2.7.18
## python3 => 3.7.7
$ pyenv global 2.7.18 3.7.7

# 現在選択されているバージョンを確認
$ pyenv versions
* 2.7.18 (set by /home/user/.anyenv/envs/pyenv/version)
* 3.7.7 (set by /home/user/.anyenv/envs/pyenv/version)

$ python --version
2.7.18

$ python --version
3.7.7

# pip パッケージマネージャを更新しておく
$ pip install --upgrade pip setuptools
$ pip3 install --upgrade pip setuptools


# --- nodenv 導入 ---

# anyenv を使って nodenv 導入
## nodenv を使うことで、複数バージョンの Node.js 環境を構築できる
$ anyenv install nodenv
$ exec $SHELL -l

## nodenv-yarn-install プラグイン導入: nodenv install 時に yarn もインストールする
$ mkdir -p "$(nodenv root)/plugins"
$ git clone https://github.com/pine/nodenv-yarn-install.git "$(nodenv root)/plugins/nodenv-yarn-install"
$ echo 'export PATH="$HOME/.yarn/bin:$PATH"' >> ~/.zshrc

# Node.js インストール可能なバージョンを確認
$ nodenv install --list

# Node.js 14.17.5 インストール
$ touch $(nodenv root)/default-packages
$ nodenv install 14.17.5

# Node.js 14.17.5 に切り替え
$ nodenv global 14.17.5

# 現在選択されているバージョンを確認
$ nodenv versions
* 14.17.5 (set by ~/.anyenv/envs/nodenv/version)

# 一度シェルを再起動しないと Node.js が使えない
$ exec $SHELL -l

# バージョン確認
$ node -v
v14.17.5

$ yarn -v
1.22.11
```

#### Setup on Windows 10
Windows 環境の場合、WSL2 を導入可能であれば WSL2 + anyenv + nodenv 環境を使うのが現状の最適解と思われる

しかし、事情により WSL2 を使えない場合もあると思われるため、ここでは WSL2 を使わない場合の Windows 10 での環境構築手順を掲載する

Windows 10 ネイティブ環境では nodenv が使えないため、[nvm-windows](https://github.com/coreybutler/nvm-windows) を使う（無論、無理して Node.js のバージョン管理システムを導入しなくても良いのだが、手動でバージョン管理しようとすると複数の案件を回そうとしたときに辛くなりやすい）

`Win` + `X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# パッケージマネージャとして Chocolatey ではなく scoop を使う
## Chocolatey で nvm を導入した場合、非管理者権限で npm グローバルインストール系のコマンドがこけることが多かったため
### (おそらく `C:\Program Files\` で node.js 周りのファイル管理をしているためと思われる)
> iwr -useb get.scoop.sh | iex

# powershell script の実行ポリシーを付与
> Set-ExecutionPolicy -ExecutionPolicy RemoteSigned

# => 設定変更を反映するため、一度 PowerShell 再起動

# scoop で nvm インストール
> scoop install nvm

# => 自動的に環境変数情報が変更されるため、再び PowerShell 再起動

# nvm で Node.js 14.17.5 インストール
> nvm install 14.17.5

# Node.js 14.17.5 を使う
> nvm use 14.17.5

# npm, yarn パッケージマネージャを更新しておく
> npm update -g npm yarn

# yarn global bin の PATH をユーザ環境変数に追加しておく
> [System.Environment]::SetEnvironmentVariable("PATH", [System.Environment]::GetEnvironmentVariable("PATH", "User") + ";$(yarn global bin)", "User")

# バージョン確認
$ node -v
v14.17.5

$ yarn -v
1.22.11
```

### Node.js で Hello, world
以上で Node.js が使えるようになったため、動作確認を兼ねて `Hello, world` を表示してみる

ファイル名は任意だが、ここでは `hello.js` というファイルを作成し、以下のように JavaScript コードを記述してみる

```bash
# hello.js ファイルを作成しながら VSCode で開く
$ code hello.js
```

```javascript
// コンソールに "Hello, world" と表示
console.log('Hello, world');
```

スクリプトファイルを作成したら、以下のコマンドで Node.js を実行する

```bash
# Node.js で hello.js ファイルを実行する
$ node hello.js
Hello, world
```

### npm でパッケージをインストールしてみる
Node.js エコシステムには便利なパッケージが多くある

ここでは、コンソールに色をつけることのできる `colors` というパッケージを `npm` でインストールして使ってみる

```bash
# npm で colors パッケージインストール
$ npm install colors

## 短縮形で `npm i colors` という書き方も可
```

上記コマンドを実行すると、カレントディレクトリに `node_modules/` ディレクトリが作成される

このディレクトリ内に各種パッケージがインストールされるという仕組みになっている

なお、一緒に `package.json` というファイルも作成されるが、このファイルにはインストールパッケージの情報等が記述されている

このファイルがあれば `node_modules/` ディレクトリを削除しても `package.json` の情報をもとに、使われていたパッケージを再インストールすることができるという仕組みになっている

```bash
# ./package.json の情報をもとにパッケージを一括インストール
## 短縮形で `npm i` という書き方も可
$ npm install
```

話がそれてしまったが、インストールした `colors` パッケージを使って、コンソールに色付き文字を表示してみる

```javascript
// hello.js

// Node.js では require 関数を使って、別ファイルに記述された JavaScript モジュール（パッケージ）を読み込むことができる
// 以下のようにして colors パッケージを読み込む
require('colors');

// 黄色テキストで "Hello, world" 表示
console.log('Hello, world'.yellow);
```

これで `node hello.js` を実行すると、黄色テキストで `Hello, world` と表示されるはずである

#### パッケージのグローバルインストールとローカルインストール
今回、パッケージは作業ディレクトリにローカルインストールしたが、作業ディレクトリ以外の JavaScript プログラムからも読み込むことができるようにグローバルインストールすることも可能である

その場合は `npm` に `-g` オプションをつけてインストールを行う

```bash
# colors パッケージをグローバルインストール
## 別ディレクトリにあるプログラムからでも require('colors') できるようにする
$ npm install -g colors

## 短縮形で `npm i -g colors` という書き方も可
```

なお、`require` でグローバルインストールしたパッケージを読み込みたい場合、環境変数 `NODE_PATH` で npm グローバルインストール先ディレクトリを設定しておく必要があるため注意が必要である

本稿では、環境構築時に `NODE_PATH` の設定を行っていないが、これは基本的にグローバルインストールを推奨していないためである

パッケージをグローバルインストールしてしまうと、他の開発環境において同じパッケージ環境を再現するのが難しくなってしまうため、本稿においては、グローバルインストールして使うのは `npm` や `yarn` のようなコマンドとして利用されることを前提としてパッケージのみとしている

今回サンプルとして使った `colors` のような、`require` で読み込んで使うパッケージは、ローカルインストールして使うことを推奨している

ローカルインストールであれば、`package.json` ファイルを共有するだけで、同じパッケージ環境を再現できるためである

### 依存パッケージのアップデート
Node.js で開発をしていると、依存パッケージが高い頻度で更新されて困ることが多い

特に GitHub リポジトリでコード管理していると、「このパッケージは脆弱性があります、そのパッケージは推奨されません」という親切なセキュリティアラートで埋め尽くされることが度々ある

こういった場合、`npm outdated` コマンドを使うことで各パッケージのアップデートを確認することはできるが、一つ一つのパッケージをすべて確認してアップデートを行うのは非常に面倒である

また通常、パッケージのアップデートは `npm update` コマンドを用いて行うが、このコマンドの問題点として **package.json に記述されたバージョンの範囲で** しか最新バージョンにアップデートしてくれないという問題がある

例えば、`"package": "^3.2.1"` などのように記述されている場合、そのパッケージの最新版として `4.0.0` がリリースされていたとしても、そのバージョンまでは上げてくれないということである

そういった場合は手動で `npm install package@4.0.0` のようにバージョンを指定して再インストールするしかない

以上のような理由で、依存パッケージのバージョン管理を行うのに `npm` コマンドだけでは労力がかかってしまうため、`npm-check-updates` というパッケージを利用すると便利である

#### npm-check-updates の導入
基本的には `npm-check-updates` パッケージをグローバルインストールして `npm-check-updates` コマンドを使えるようにすれば良い

```bash
# npm-check-updates のグローバルインストール
$ npm i -g npm-check-updates

# 以降、`npm-check-updates` コマンドが使えるようになる
$ npm-check-updates -h
Usage: npm-check-updates [options] [filter]
  :
```

しかし、正直 `npm-check-updates` コマンドはそれほど頻繁に使うようなコマンドではないため、あまりグローバルインストールして環境を汚したくない、という場合も多い

そういった場合に便利なコマンドとして `npx` というコマンドが用意されている

これは `npm` コマンドをより簡単に使えるように拡張されたコマンドで、`npm@5.2.0` から同梱されており、Node.js 8.2 以降であればデフォルトでインストールされているはずである

`npx` は以下のような機能を有している

- `run-script` を使用せずにローカルインストールしたコマンドを実行可能
- グローバルインストールせずに一度だけコマンドを実行可能
- GitHub や Gist で公開されているコマンドを実行可能

上記2番目の機能を利用することで、グローバルインストールすることなく一度だけ `npm-check-updates` コマンドを実行することが可能である

```bash
# グローバルインストールせずに一度だけ npm-check-updates コマンドを実行
$ npx npm-check-updates -h
Ok to proceed? (y) # <= そのまま Enter して実行

Usage: npm-check-updates [options] [filter]
  :
```

#### npm-check-updates による package.json の更新
`npm-check-updates` コマンドの動作原理は単純で、`package.json` に記述された各パッケージのバージョン情報を確認し、最新バージョンがあれば `package.json` に記述された各パッケージのバージョン情報を書き換えるだけである

これにより `npm install` コマンドを実行するだけで最新バージョンのパッケージが再インストールされる、という仕組みである

```bash
# ncu でアップデート可能なパッケージの確認
$ npx npm-check-updates -c 'ncu'
Checking package.json
  :

# ncu -u を実行すると package.json が更新される
$ npx npm-check-updates -c 'ncu -u'
Checking package.json
  :
Run npm install to install new versions.

# 更新された package.json をもとに npm install の実行
## => 最新パッケージが再インストールされる
$ npm install
```
