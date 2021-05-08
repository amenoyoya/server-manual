# Node.js 入門

## npm package 登録

Node.js で作成したアプリケーションやライブラリ等をパッケージマネージャでインストールできるようにするためには、Npm.js に公開登録する必要がある

- 参考: [【npm】簡単なnpmパッケージを作る](https://okinawanpizza.hatenablog.com/entry/2020/01/23/170807)

### Npm.js へのアカウント登録
https://www.npmjs.com/signup からアカウントの登録を行う

基本的に Username, Email は GitHub アカウントと同一のものにしておいた方が分かりやすい

Username, Email, Password を入力して送信すると、入力したメールアドレス宛に本登録用のURLが送信されてくるため、Verify する

### npm コマンドにアカウント追加
`npm publish` (npm package を公開するコマンド) を実行できるようにするために、登録したアカウントをコマンドに登録する

```bash
# npm コマンドにアカウント追加
$ npm adduser

Username: # <= 登録した Username 入力
Password: # <= 登録した Password 入力
Email: (this IS public) # <= 登録した Email 入力

# npm init 時にデフォルトで設定されるユーザ情報をついでに設定しておく
$ npm set init.author.name '<登録したユーザ名>'
$ npm set init.author.email '<登録したメールアドレス>'
$ npm set init.author.url '<運営しているホームページURL（任意）>'
```

### GitHub にリポジトリを作成してクローン
npm package 登録には、カレントディレクトリに `package.json` さえあれば基本的に問題ないが、慣例的には GitHub にリポジトリを作成しておき、ソースコード管理できるようにしておくことが多い

そのため、GitHub に空のリポジトリを作成してローカルにクローンすることから始めるのが良い

```bash
# http://github.com/<username>/<repository>.git を clone
$ git clone git@github.com:<username>/<repository>.git

$ cd <repository>
```

### package.json 作成
プロジェクトルートディレクトリに `package.json` を作成する

基本的には `npm init` コマンドで作るのが楽だが、手動で作成する場合は、npm package 登録に必要な以下の情報は最低限記述しておくこと

```json
{
  "name": "<パッケージ名（なるべくユニークな名前にしておいた方が良い）>",
  "version": "<バージョン（基本的に Git のリリースバージョンに合わせること）>",
  "description": "<説明（省略可）>",
  "keywords": ["<node, http 等のキーワード（省略可）>"],
  "license": "<ライセンス（省略可だが、特に気にしないのであれば MIT ライセンスにしておくのが無難）>",
  "main": "<パッケージのエントリーポイントファイル。requireするときのメインファイル>",
  "repository": {
    "type": "git",
    "url": "<GitHubリポジトリURL。GitHub等でソースコード管理しない場合は repository 項目自体不要>"
  },
  "author": {
    "name": "<Npm.jsに登録したユーザ名>",
    "email": "<Npm.jsに登録したメールアドレス>",
    "url": "<自身のホームページURLか、GitHubリポジトリのURL>"
  }
}
```

### Npm.js へのアップロード
実装が完了したら、`package.json` の `version` 情報を更新し、`npm publish` で公開登録を行う

```bash
# ※以下はバージョン 0.1.0 でリリースする場合の例

# Git リリースコミット
$ git add --all
$ git commit -m 'release/v0.1.0'
$ git push origin master

# 可能であれば GitHub の Release 機能を利用して、`v0.1.0` のような形式でバージョンタグを付与すると良い
$ git tag v0.1.0
$ git push origin v0.1.0

# Npm.js にアップロード
$ npm publish
```

なお、内容を修正して再び `npm publish` したい場合は、必ず `package.json` の `version` 情報を更新しておく必要がある
