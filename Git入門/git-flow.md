# git-flow 入門

## ブランチモデル

複数人での開発で、特になんの決まりもなくブランチを使っていると、無秩序にブランチの作成やマージが行われ、リポジトリが混沌としてくる

こうした問題を解決するために、 **ブランチモデル** というブランチ管理方法が考案された

**git-flow** は、ブランチモデルの中でも比較的歴史の長いモデルである

***

## git-flow の概要

git-flow は、正確にいうと Vincent Driessen 氏が提唱する「**A successful Git branching model**」というブランチモデルをサポートするツール（コマンド）の名称

一般的には、モデルとツールのどちらの名称としても使われている

git-flow では、役割が決められた5種類（場合によっては6種類）のブランチを切り替えながら開発を進めていく

ブランチの作成やマージに決まりを設けることで、複数人での開発時にもブランチをわかりやすい状態に保つことができ、不用意なマージによる問題を避けることが可能

***

## git-flow で定義されているブランチ

### master ブランチ
完成したコードをマージするためのブランチ（製品リリース用のブランチ）

このブランチに直接コミットしてはならない

### develop ブランチ
開発の中心になるブランチ

開発中は、develop ブランチから各作業者が feature ブランチを切り、作業完了後に再びマージするというサイクルを繰り返すことになる

master ブランチ同様、このブランチに直接コミットしてはならない

### feature ブランチ
機能の追加や変更、バグフィックス等を行うブランチ

一つの変更に対して一つの feature ブランチを切ることになるため、開発中で最も使われるブランチとなる

ブランチ名は `feature/***(開発・作業名)` のように命名するのが一般的

基本的に develop ブランチから切り、作業完了後は develop ブランチにマージしてから削除する流れとなる

### release ブランチ
製品のリリース時に関連する作業（バージョン情報を入れるなど）を行うためのブランチ

製品リリース時に、develop ブランチから release ブランチを切って作業を行う

作業完了後は、master ブランチと develop ブランチにマージし、master ブランチにリリースバージョンのタグを設定する

その後、release ブランチは削除する

### hotfix ブランチ
製品リリース後に重大な不具合が見つかり、緊急の修正を行う際に切るブランチ

master ブランチから直接 hotfix ブランチを切り、作業完了後は master ブランチと develop ブランチにマージする

マージ完了後は、master ブランチにリリースバージョン（マイナーバージョン）のタグを設定し、hotfix ブランチは削除する

### support ブランチ（オプション）
旧バージョンの保守を続ける必要があるときに使うブランチ

サポートが必要なバージョンの master ブランチのコミットから派生させ、サポートを終了するまで独立してバグフィックスやリリースを行う

---

## git-flow実践

実際に開発を行いながらgit-flowを実践してみる

このチュートリアルは https://tracpath.com/bootcamp/learning_git_git_flow.html からの転載である

### 開発するプログラムの仕様
FizzBuzz問題をHTMLおよびJavascriptで実装する
- フォームに入力された数字が3で割り切れるなら「Fizz」を表示
- フォームに入力された数字が5で割り切れるなら「Buzz」を表示
- フォームに入力された数字が3でも5でも割り切れるなら「FizzBuzz」を表示
- 上記のいずれにも該当しない場合、入力された数字をそのまま表示

### Gitリポジトリの作成
```bash
$ mkdir git-flow-training  # 作業ディレクトリ git-flow-training を作成
$ cd git-flow-training  # 作業ディレクトリに移動

# gitのユーザー名やメールアドレスを設定していない場合は、先に設定しておく
$ git config --global user.name '<ユーザー名>'
$ git config --global user.email '<メールアドレス>'

# Gitリポジトリ作成（.gitフォルダが生成される）
$ git init
```

### Initial commit
最初のコミット（ **Initial commit** ）をするために、`README.md` を作成し、プロジェクトの概要を記述する

```markdown
# FizzBuzz

FizzBuzz問題をHTMLおよびJavascriptで実装

## 仕様
- フォームに入力された数字が3で割り切れるなら「Fizz」を表示
- フォームに入力された数字が5で割り切れるなら「Buzz」を表示
- フォームに入力された数字が3でも5でも割り切れるなら「FizzBuzz」を表示
- 上記のいずれにも該当しない場合、入力された数字をそのまま表示
```

master ブランチに Initial commit する

```bash
# 以下作業ブランチ名を()で表示する

# 変更箇所の確認
(master)$ git status
  new file: README.md  # ステージングに追加されていないので赤で表示されているはず

# 変更したファイルを全てステージングに追加
(master)$ git add --all

# ステージングに追加されたので緑で表示されているはず
(master)$ git status
  new file: README.md

# コミットメッセージ "Initial commit" でコミット
(master)$ git commit -m 'Initial commit'
```

### git-flow 開始
最初の操作としては、最初からある master ブランチから develop ブランチを切るだけ

ただし、git-flow ツールの初期設定として `git flow init` を実行し、各ブランチの名前を決める必要がある

```bash
# git flow コマンドを使わず手作業で git-flow の開発サイクルを実践する場合は以下の作業は不要
## 可能なら手作業で git-flow の開発の流れを掴んでおくと、作業の流れがよく分かるため推奨する

# git-flow 開発開始
(master)$ git flow init
No branches exist yet. Base branches must be created now.
Branch name for production releases: [master] # <= そのままEnter
Branch name for "next release" development: [develop] # <= そのままEnter

How to name your supporting branch prefixes?
Feature branches? [] # <= feature を指定
Bugfix branches? [] # <= bugfix を指定
Release branches? [] # <= release を指定
Hotfix branches? [] # <= hotfix を指定
Support branches? [] # <= support を指定
Version tag prefix? [] # <= そのままEnter
Hooks and filters directory? [~/dev/git-flow-training/.git/hooks] # <= そのままEnter

    # -- 手作業で行う場合 -- #
    # master ブランチから develop ブランチを新規作成して切り替え
    (master)$ git checkout -b develop

# ブランチの確認
# 作業ブランチがdevelopに切り替わっていることを確認
(develop) git branch
* develop
  master
```

### featureブランチを切ってアプリケーションのベースを作成
アプリケーションのベースを作るために、`implement-app-base` という feature ブランチを develop ブランチから切る

```bash
# feature ブランチを切り、作業開始
(develop)$ git flow feature start implement-app-base

    # -- 手作業で行う場合 -- #
    (develop)$ git checkout -b feature/implement-app-base

# develop ブランチから feature ブランチが切られたことを確認
(feature/implement-app-base)$ git branch -v  # ブランチの詳細確認
  develop                    9bb327d Initial commit
* feature/implement-app-base 9bb327d Initial commit
  master                     f8ece92 Initial commit
```

feature ブランチ上で、アプリケーションのベースを作成する

`index.html` を新規作成し、以下のように記述する

```html
<!-- index.html -->
<!doctype html>
<html>

<head>
    <meta charset="utf-8">
    <title>Fizz Buzz v1.0</title>
</head>

<body>
    <div>
        <input type="text" id="input-number">
        <button id="run">Run</button>
    </div>
    <div>
        <p id="output"></p>
    </div>
    <script src="https://code.jquery.com/jquery-1.12.0.min.js"></script>
    <script src="app.js"></script>
</body>

</html>
```

この変更を feature ブランチにコミットする

```bash
(feature/implement-app-base)$ git add --all
(feature/implement-app-base)$ git commit -m "アプリのベースを作成しました"

(feature/implement-app-base)$ git branch -v
  develop                    9bb327d Initial commit
* feature/implement-app-base cb84ec6 アプリのベースを作成しました
  master                     f8ece92 Initial commit
```

develop ブランチにマージして、feature ブランチを削除する

```bash
# featureブランチでの作業完了
(feature/implement-app-base)$ git flow feature finish implement-app-base

    # -- 手作業で行う場合 -- #
    (feature/implement-app-base)$ git checkout develop  # developブランチに切り替え
    (develop)$ git merge feature/implement-app-base  # featureブランチをマージ
    (develop)$ git branch -d featureimplement-app-base  # featureブランチを削除

    # ※実のところ、feature ブランチは削除せずに残しておくことが多い（作業の修正を同じブランチで行うため）
    # ※そのため、git flow コマンドを使わず、手動で git-flow 開発を行うことが多い

# featureブランチがdevelopブランチにマージされ、featureブランチは削除されている
(develop)$ git branch -v
* develop cb84ec6 アプリのベースを作成しました
  master  f8ece92 Initial commit
```

### 再びfeatureブランチを切ってアプリケーションの機能を実装
JavaScript で機能を実装していく

再び develop ブランチから feature ブランチを切り、実装作業を行う

ブランチ名は `implement-fizz-buzz` とする

```bash
# featureブランチを切り、作業開始
(develop)$ git flow feature start implement-fizz-buzz

    # -- 手作業で行う場合 -- #
    (develop)$ git checkout -b feature/implement-fizz-buzz

# developブランチからfeatureブランチが切られたことを確認
(feature/implement-fizz-buzz)$ git branch -v  # ブランチの詳細確認
  develop                     cb84ec6 アプリのベースを作成しました
* feature/implement-fizz-buzz cb84ec6 アプリのベースを作成しました
  master                      f8ece92 Initial commit
```

feature ブランチ上で、実装作業を行う

`app.js` を新規作成し、以下のように記述する

```javascript
// app.js
$("#run").click(function () {
    var n = $("#input-number").val();
    if(n % 3 === 0 && n % 5 === 0) {
        $("#output").text("FizzBuzz");
    }
    else if(n % 3 === 0) {
        $("#output").text("Fizz");
    }
    else if(n % 5 === 0) {
        $("#output").text("Buzz");
    }
    /* 数字をそのまま表示する機能が未実装だが、とりあえず無視 */
});
```

実装が終わったら、コミットする

```bash
(feature/implement-fizz-buzz)$ git add --all
(feature/implement-fizz-buzz)$ git commit -m "FizzBuzzを実装しました"

(feature/implement-fizz-buzz)$ git branch -v
  develop                     cb84ec6 アプリのベースを作成しました
* feature/implement-fizz-buzz a74b238 FizzBuzzを実装しました
  master                      f8ece92 Initial commit
```

作業を完了する

```bash
# featureブランチでの作業完了
(feature/implement-fizz-buzz)$ git flow feature finish implement-fizz-buzz

    # -- 手作業で行う場合 -- #
    (feature/implement-fizz-buzz)$ git checkout develop  # developブランチに切り替え
    (develop)$ git merge feature/implement-fizz-buzz  # featureブランチをマージ
    (develop)$ git branch -d feature/implement-fizz-buzz  # featureブランチを削除

# featureブランチがdevelopブランチにマージされ、featureブランチは削除されている
(develop)$ git branch -v
* develop a74b238 FizzBuzzを実装しました
  master  f8ece92 Initial commit
```

### リリースする
一通り実装が終わったので、バージョン 1.0 としてリリースする

そのために、release ブランチを切る

```bash
# バージョン1.0のreleaseブランチを切る
(develop)$ git flow release start 1.0

    # -- 手作業で行う場合 -- #
    (develop)$ git checkout -b release/1.0

# developブランチからreleaseブランチが切られたことを確認
(release/1.0)$ git branch -v
  develop     a74b238 FizzBuzzを実装しました
  master      f8ece92 Initial commit
* release/1.0 a74b238 FizzBuzzを実装しました
```

release ブランチ上でリリースに関連する作業を行う

ここでは、タイトルにバージョン情報を追記する

```html
<!-- index.html -->
<title>Fizz Buzz v1.0</title>
```

リリース関連作業が終わったら、変更をコミット

```bash
(release/1.0)$ git add --all
(release/1.0)$ git commit -m "バージョン番号を追加しました"
```

リリースする（以下の順でコマンドが実行される）

1. release ブランチを master ブランチにマージ
2. master ブランチにリリースタグ付け
3. master ブランチ（タグ1.0）を develop ブランチにマージ
4. release ブランチの削除

```bash
# リリースする
(release/1.0)$ git flow release finish 1.0

    # -- 手作業で行う場合 -- #
    (release/1.0)$ git checkout master  # masterブランチに切り替え
    (master)$ git merge release/1.0  # releaseブランチをマージ
    (master)$ git tag -a 1.0 -m "初回リリース"  # "初回リリース" コメント付きタグ `1.0` を付与
    (master)$ git checkout develop  # developブランチに切り替え
    (develop)$ git merge master  # masterブランチをマージ
    (develop)$ git branch -d release/1.0  # releaseブランチを削除

# これまでのブランチの流れを確認
(develop)$ git log --oneline --graph  # 1ブランチ1行, グラフ形式で表示
*     55bd60b (HEAD -> develop) バージョン番号を追加しました
|\ 
| *   3efd0bf (tag: 1.0, master) バージョン番号を追加しました
| |\
| | * 72a2423 バージョン番号を追加しました
| |/
|/|
* | a74b238 FizzBuzzを実装しました
* | cb84ec6 アプリのベースを作成しました
* | 9bb327d Initial commit
|/
* f8ece92 Initial commit
```

### 緊急バグ修正
バージョン 1.0 をリリースした後にバグが発覚し、緊急対応しなければならなくなった

すでにお気づきの通り、このアプリケーションには「3でも5でも割り切れないときに数字をそのまま表示する」機能が抜けている

このような緊急的な修正は、hotfix ブランチでバグフィックスする

ブランチ名は、マイナーバージョンである `1.1` とする

```bash
# masterブランチからhotfixブランチを切る
(develop)$ git checkout master
(master)$ git flow hotfix start 1.1

    # -- 手作業で行う場合 -- #
    (master)$ checkout -b hotfix/1.1

# masterブランチからhotfixブランチが切られたことを確認
(hotfix/1.1)$ git branch -v
  develop    55bd60b バージョン番号を追加しました
* hotfix/1.1 3efd0bf バージョン番号を追加しました
  master     3efd0bf バージョン番号を追加しました
```

hotfix ブランチ上で修正作業を行う

`app.js` を以下のように修正

```javascript
// app.js
$("#run").click(function () {
    var n = $("#input-number").val();
    if(n % 3 === 0 && n % 5 === 0) {
        $("#output").text("FizzBuzz");
    }
    else if(n % 3 === 0) {
        $("#output").text("Fizz");
    }
    else if(n % 5 === 0) {
        $("#output").text("Buzz");
    }
    /* v1.1 で修正: 数字をそのまま表示 */
    else {
        $("#output").text(n);
    }
});
```

修正をコミット

```bash
(hotfix/1.1)$ git add --all
(hotfix/1.1)$ git commit -m "数字が表示されないバグを修正しました"
```

修正をマージしてリリースする（以下の順でコマンドが実行される）

1. hotfix ブランチを master ブランチにマージ
2. master ブランチにリリースタグ付け
3. master ブランチ（タグ1.1）を develop ブランチにマージ
4. hotfix ブランチの削除

```bash
# hotfixブランチを完了
(hotfix/1.1)$ git flow hotfix finish 1.1

    # -- 手作業で行う場合 -- #
    (hotfix/1.1)$ git checkout master  # masterブランチに切り替え
    (master)$ git merge hotfix/1.1  # hotfixブランチをマージ
    (master)$ git tag -a 1.1 -m "緊急バグフィックスリリース"  # コメント付きタグ`1.1`を付与
    (master)$ git checkout develop  # developブランチに切り替え
    (develop)$ git merge master  # masterブランチをマージ
    (develop)$ git branch -d hotfix/1.1  # hotfixブランチを削除

# これまでのブランチの流れを確認
(develop)$ git log --oneline --graph
*   08d0e78 (HEAD -> develop) 数字が表示されないバグを修正しました
|\
| *   5aa183e (tag: 1.1, master) 数字が表示されないバグを修正しました
| |\
| | * 6013ab7 数字が表示されないバグを修正しました
| |/
* |   55bd60b (HEAD -> develop) バージョン番号を追加しました
|\ \
| |/
| *   3efd0bf (tag: 1.0, master) バージョン番号を追加しました
| |\
| | * 72a2423 バージョン番号を追加しました
| |/
|/|
* | a74b238 FizzBuzzを実装しました
* | cb84ec6 アプリのベースを作成しました
* | 9bb327d Initial commit
|/
* f8ece92 Initial commit
```
