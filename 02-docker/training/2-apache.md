# Apacheサーバ入門

## Apacheサーバ基礎知識

### Apacheサーバ設定ファイル
Apacheサーバの設定ファイルの置き場所は、RedHat系Linux（CentOS等）とDebian系Linux（Ubuntu等）で大きく変わるため、なれないうちは戸惑うことが多い

基本的には、以下のような構成になっていることを覚えておけば良い

```bash
# --- RedHat系 ---
/etc/httpd/
 |_ conf/
 |  |_ httpd.conf # メイン設定ファイルで /etc/httpd/conf.d/*.conf ファイルを include する
 |                ## ※ このファイル自体を編集することは基本的にない
 |_ conf.d/ # ユーザ設定ファイルを置くディレクトリ
            ## カスタマイズ設定は、この中に .conf ファイル作成して記述する


# --- Debian系 ---
/etc/apache2/
 |_ apache2.conf # メイン設定ファイルで以下の設定ファイルをincludeする
 |               ## - /etc/apache2/mods-enabled/*.conf
 |               ## - /etc/apache2/sites-enabled/*.conf
 |               ## - /etc/apache2/conf.d/*.conf
 |               ## - /etc/apache2/ports.conf
 |               ## ※ このファイル自体を編集することは基本的にない
 |
 |_ ports.conf # ポート設定（デフォルトでは80番ポートを開いている）
 |
 |_ mods-enabled/     # 現在有効なApache2のモジュールを置くディレクトリ
 |  |_ (*.conf) <───┐ ## 通常、この中に直接 .conf ファイルを作成することはない
 |                  │
 |                  │ # <= a2enmod コマンドで mods-available/*.conf のリンクを貼ることで設定を有効化する
 |                  │
 |_ mods-available/ │ # Apacheにインストールされた（利用可能な）モジュール置き場
 |  |_ *.conf ──────┘
 |
 |_ sites-enabled/     # 現在有効なWebサイト固有設定を置くディレクトリ
 |  |_ (*.conf) <────┐ ## 通常、この中に直接 .conf ファイルを作成することはない
 |                   │
 |                   │ # <= a2ensite コマンドで sites-available/*.conf のリンクを貼ることで設定を有効化する
 |                   │ ## ※ 000-default.conf だけは最初からリンクが貼られている（有効化されている）
 |                   │
 |_ sites-available/ │ # 利用可能なWebサイト固有設定ファイル置き場
 |  |_ *.conf ───────┘
 |
 |_ conf.d/ # Apache追加設定ファイルを置くディレクトリ
```

### .htaccess ファイル
`.htaccess` ファイル (**分散設定ファイル**) は、ディレクトリ毎にサーバ設定を変更するためのファイルである

大本のApache設定ファイルで `AllowOverride` と `FollowSymLinks` が有効化されている必要がある

```ruby
AllowOverride All
Options +FollowSymLinks
```

なお、Apache公式ドキュメントでも言われているが、基本的には `.htaccess` を使わないことが推奨されている

なぜなら大本のApache設定ファイルで同等の設定が可能な上、Web上にこういった設定ファイルが公開可能な状態になっていることはセキュリティ上よろしくないためである

以下に `.htaccess` ファイルのメリット・デメリットをまとめる

- メリット:
    - レンタルサーバ等、大本のApache設定ファイルの編集権限がない場合でも利用可能
    - Apacheサーバの再起動不要で設定が反映される
    - プロジェクトディレクトリで Git 管理が可能（ただしあまり Git 管理はしないほうが良い）
- デメリット:
    - Web上に公開可能なため、セキュリティ上の懸念がある
    - アクセスがある度に設定を読み込むため、設定が増えるほどパフォーマンスが低下する

### リダイレクト制御
Apacheサーバでリダイレクト（あるURLへのアクセスを別のURLへ変更）を行いたい場合は、`mod_redirect` モジュールを有効化する必要がある

通常、`mod_redirect` モジュールはApacheサーバインストール時に一緒にインストールされるが、Debian系Linuxの場合は、明示的にモジュールを有効化する必要があるので注意

```bash
# Debian系Linuxの場合 mod_redirect を明示的に有効化
$ sudo a2enmod redirect

# => mods-available にある redirect モジュールの設定ファイルのリンクが
##   mods-enabled 配下に作られて有効化される
```

リダイレクトに関する設定は、RedHat系Linuxの場合 `/etc/httpd/conf.d/*.conf`、Debian系Linuxの場合 `/etc/apache2/sites-available/*.conf`, `/etc/apache2/conf.d/*.conf` に置くのが基本的で、どうしても必要であれば `.htaccess` に記述する

リダイレクト設定は通常、SEO的な観点から以下のような正規化のために使われることが多い

- www あり・なしの正規化
    - 大抵のサイトでは `www.example.com` と `example.com` は同じ内容で運営される
    - すると、www あり・なしの2つのサイトに検索エンジンのページ評価が分散してしまうのは望ましくない
    - そういった場合に www あり or なし のいずれかにアクセスを統一するとSEO上良いとされる
- トレーリングスラッシュの正規化
    - URL末尾の `/` はあってもなくても通常は同じアクセスとみなされ、同じページが表示される
    - そのため www 正規化と同様に、ページ評価分散を避けるために `/` あり or なし に統一することが望ましい
- 常時SSL化
    - サイト全体をHTTPS化（`http://...` ではなく `https://....` での通信に）することを常時SSL化と呼ぶ
    - 近年のインターネットセキュリティ意識の高まりや主要なブラウザの対応に伴い、サイトの常時SSL化は必須のものとなっている
    - そのため、`http://` のアクセスは `https://` にリダイレクトすることが望ましい
        - ※ その前にSSL証明書を取得して SSL/TLS 通信可能な状態にしておく必要がある

```ruby
# IfModuleディレクティブで mod_rewrite が有効化されているか判定可能
## ※ 基本的にはモジュール有効化されている前提で設定を書くことが望ましい
## => IfModuleディレクティブはあまり書かないほうが良いとされている
##    (モジュール無効化されていればエラーとなり、そこでモジュールが無効化されていることに気付けるため)
<IfModule mod_rewrite.c>
  # RewriteEngine有効化
  RewriteEngine on

  # リダイレクトの基本書式は以下のようになる
  # RewriteRule <パターン> <置換後> [<フラグ>]
  ## `http://localhost/index.php` にアクセスした場合、RewriteRuleは引数 `index.php` を受け取る
  ## パターンは正規表現で書く
  ##  - ホスト/index.php を書き換えたいなら ^index\.php$
  ## 置換後のディレクトリパスは RewriteBaseで指定（デフォルトは /var/www/html/）
  ##  - 置換後の文字列に hoge.php を指定すると /var/www/html/hoge.php に置換される
  ## 302リダイレクトする場合、フラグには [R=302] を指定する

  # 常時SSL化
  ## HTTPS 通信が on でないなら
  RewriteCond %{HTTPS} !on
  ## https://... にリダイレクト(301: 恒久的リダイレクト
  RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [R=301,L]

  # www ありに正規化
  ## HTTP_HOST が www なしで始まっている場合
  RewriteCond %{HTTP_HOST} ^example\.com$
  ## www ありのURLにリダイレクト(301: 恒久的リダイレクト)
  RewriteRule ^(.*)$ https://www.example.com/$1 [R=301,L]

  # トレーリングスラッシュありに正規化
  ## リクエストURIが実体ファイルでなく、
  RewriteCond %{REQUEST_FILENAME} !-f
  ## かつ、URI末尾が / で終わっていない場合
  RewriteCond %{REQUEST_URI} (.+[^/])$
  ## 末尾スラッシュを付与したURLにリダイレクト(301: 恒久的リダイレクト)
  RewriteRule ^ %1/ [R=301,L]
</IfModule>
```

### VirtualHost
Apacheサーバで複数サイトを運営するための仕組み

ホスト名でHTTPアクセス先を振り分けることが可能

基本的には、以下のような書式で定義する

```ruby
# ポート80番（HTTPアクセス）のVirtualHost定義
<VirtualHost *:80>
  # 振り分け用ホスト名設定
  ServerName example.com
  
  # 振り分け先のドキュメントルート定義
  ## このディレクトリ配下の index.html(.php) を読み込んで表示する
  DocumentRoot /var/www/html/

  # ドキュメントルートディレクトリ内の設定（任意）
  <Directory /var/www/html/>
    # indexの存在しないディレクトリアクセス時、ファイルリストを表示させない: -Indexes
    ## ※ これを設定しないと、index.html(.php) の存在しないディレクトリの中身が丸見えになってしまう
    # コンテントネゴシエーション無効化: -MultiViews
    ## ※ これが有効化されていると拡張子なしURIを拡張子有ファイルにリダイレクトできない
    # シンボリックリンク + .htaccess 有効化: +FollowSymLinks
    Options -Indexes -MultiViews +FollowSymLinks

    # .htaccess を有効化
    AllowOverride All

    # WEBアクセスを許可
    Require all granted
  </Directory>
</VirtualHost>
```

### SSI (Server Side Includes)
mod_php などが提供される以前の技術で、分離されたHTMLファイルを読み込んだり、簡単なコマンドを実行して、動的なコンテンツを生成することができる

セキュリティ的に脆弱な部分も多く、現在では mod_php や fastCGI などで代替されている

SSI を利用するには、`a2enmod include` コマンドで mod_include を有効化した上で、設定ファイルに `Options +Includes` を設定する必要がある

また、SSI ディレクティブで解析されるファイル拡張子を以下のように設定する必要がある

```ruby
# .shtml ファイルを html ファイルとして扱う
AddType text/html .shtml

# .shtml ファイルで SSI ディレクティブ解析を有効化する
AddOutputFilter INCLUDES .shtml
```

SSI ディレクティブの基本的な書き方は以下の通り

```html
<!--#element attribute=value attribute=value ... -->

<!-- 例: 他のファイルを読み込んで表示する -->
<!--#include virtual="otherfile.html" -->
```

***

## 演習用 Apache 環境構築

php:7.4-apache イメージを使って、webサービスコンテナのみ作成する

また、本来 `.htaccess` ファイルは使用しないのが無難だが、設定ファイル設定の反映のために毎回コンテナを再起動するのは手間であるため、本演習では `.htaccess` ファイルを使ったアクセス制御を行う（php:7.4-apache ベースコンテナでは、Apacheの再起動 = コンテナの再起動）

### 構成
[3-apache](./3-apache) を参照

```bash
3-apache/
|_ docker/ # dockerコンテナ設定
|  |_ web/
|     |_ conf/
|     |  |_ 000-default.conf # Apacheデフォルトサイト設定
|     |  |                   # => service://web:/etc/apache2/sites-available/000-default.conf
|     |  |                   # => link to service://web:/etc/apache2/sites-enabled/000-default.conf
|     |  |_ php.ini # PHP設定ファイル => service://web:/usr/local/etc/php/php.ini
|     |
|     |_ Dockerfile # webサービスのビルド手順
|
|_ html/ # => service://web:/var/www/html/
|  |_ .htaccess # 分散設定ファイル
|  |            ## 設定反映の手間を考えて .htaccess ファイルでアクセス制御する
|  |_ index.php # http://localhost で表示されるファイル
|
|_ docker-compose.yml # dockerコンテナ構成
                      # - web: php:7.4-apache
                      #   - http://localhost => service://web:80
```

### Dockerコンテナ起動
```bash
# -- user@localhost

# 3-apache ディレクトリに移動
# cd /path/to/3-apache/

# 他のDockerコンテナは停止しておく
$ docker stop $(docker ps -q)

# コンテナビルド
$ docker-compose build

# コンテナ起動
$ docker-compose up -d

# 念の為コンテナ起動ログを確認し、エラーが起きていないか確認
$ docker-compose logs -f web

# => http://localhost にアクセスして表示に問題ないか確認
# => 問題なさそうなら Ctrl+C でログフォロー終了
```

***

## リダイレクト処理演習

Apacheの `mod_rewrite` 機能を使い、リダイレクト処理を実現する

### 演習課題 01
現状 indexファイルは省略しても省略しなくてもアクセス可能である（http://localhost と http://localhost/index.php は同じページとなる）

これは前述の通り、SEO上望ましくない

そのため、`index.php(.html)` は基本的に省略された状態に正規化することにする

リダイレクトの方式は 302 (一時的リダイレクト) とする

**※ 本来は 301: 恒久的リダイレクト を設定するのが望ましいが、301リダイレクトはブラウザのキャッシュがしつこく残る性質があり、演習には不向きであることから、今回は302リダイレクトを設定する**

#### URIの設計指針
- プログラミング言語依存の拡張子を利用しない
    - 今回の場合、`index.php` や `index.html` はプログラミング言語依存の拡張子であるため望ましくない
    - => indexファイルへのアクセスは省略されたURIのほうが望ましい
- 実装依存のパス名を利用しない（cgi-bin, servletなど）
- プログラミング言語のメソッド名を利用しない
- セッションIDを含めない
- リソースを表現する名詞で構成する

#### 実装
[3-apache/training/01/html/.htaccess](./3-apache/training/01/html/.htaccess) 参照

#### 動作確認
- http://localhost/index.php
    - http://localhost にリダイレクトされることを確認する
    - ページの内容は `var/www/html/index.php` となる
- http://localhost/index.html (本演習では `index.html` は実際には存在しない)
    - http://localhost にリダイレクトされることを確認する
    - ページの内容は `var/www/html/index.php` となる
- http://localhost
    - そのまま `var/www/html/index.php` となる

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
-   - ./html/:/var/www/html/:rw
+   - ./training/01/html/:/var/www/html/
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# .htaccessファイルの記述ミス等がすぐわかるようにログフォローしておくと良い
$ docker-compose logs -f web
```

### 演習課題 02
- `index.php`
    - GETパラメータ `q` を表示するプログラムを実装
        - http://localhost/?q=123 にアクセスされたら `123` を表示
    - GETパラメータ `q` が渡されていない場合は `不正なアクセス` と表示
- `.htaccess`
    - http://localhost/{id} にアクセスされたら http://localhost/?q={id} と同等のページを表示
        - `{id}` の仕様: `0`～`9` の数値の1個以上の繰り返し
        - リダイレクトはしない（URIは書き換えない）
        - 末尾スラッシュはあってもなくても同一のページを表示（スラッシュあり・なしを統一する必要はない）
    - 演習課題 01 と同様に `index.php(.html)` は省略された状態に正規化（302リダイレクト）

#### 実装
[3-apache/training/02/html/](./3-apache/training/02/html/) 参照

#### 動作確認
- http://localhost
    - `不正なアクセス` と表示されることを確認
- http://localhost/?q=abc
    - `abc` と表示されることを確認
- http://localhost/index.php?q=123
    - http://localhost/?q=123 にリダイレクトされることを確認
    - `123` と表示されることを確認
- http://localhost/456
    - `456` と表示されることを確認
    - リダイレクトされない（URIが書き換わらない）ことを確認
- http://localhost/910/
    - `910` と表示されることを確認
    - リダイレクトされない（URIが書き換わらない）ことを確認
- http://localhost/def/
    - 404 Not Found エラーになることを確認

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
-   - ./html/:/var/www/html/:rw
+   - ./training/02/html/:/var/www/html/
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# .htaccessファイルの記述ミス等がすぐわかるようにログフォローしておくと良い
$ docker-compose logs -f web
```

***

## Apacheで環境変数設定

Apacheの設定ファイルから環境変数を設定できる

MySQLデータベースへの接続ID・パスワードなどは、ソースコード上に直接記述せず、環境変数で定義するのがセキュリティ上好ましいとされている

これは、ソースコードが何かの拍子に盗まれたり、WEB上で閲覧できてしまったりしたりした場合のセキュリティリスクを低減できるためである

環境変数の設定は以下の書式で記述される

```ruby
SetEnv <環境変数名> <環境変数値>
```

### 演習課題 03
- `index.php`
    - 環境変数 `APP_ENV` の内容を表示するように実装
- `.htaccess`
    - 環境変数 `APP_ENV` の値を `LOCAL` に設定
    - ※ 前述のセキュリティリスクのことを考えると環境変数は本来 `.htaccess` に記述するべきではない
        - 今回は動作確認のしやすさを優先して `.htaccess` に記述する
        - 代わりに、万が一にも `.htaccess` ファイルにアクセスされないように設定すること

#### 実装
[3-apache/training/03/html/](./3-apache/training/03/html/) 参照

#### 動作確認
- http://localhost
    - `LOCAL` と表示されることを確認
- http://localhost/.htaccess
    - 403 Forbidden Error が表示されることを確認

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
-   - ./html/:/var/www/html/:rw
+   - ./training/03/html/:/var/www/html/
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# .htaccessファイルの記述ミス等がすぐわかるようにログフォローしておくと良い
$ docker-compose logs -f web
```

***

## Apacheログのカスタマイズ方法

php:7.4-apache ベースのDockerコンテナでは、以下の場所にログが出力されている

- アクセスログ: `/var/log/apache2/access.log`
- エラーログ: `/var/log/apache2/error.log`

これらのログはApache設定ファイルにより、出力内容をカスタマイズしたり、出力先を変更したりすることができる

```ruby
# アクセスログ出力内容の定義
LogFormat <出力形式定義> <出力形式名（任意名）>

## 主に使える出力形式変数（全て確認する場合は http://httpd.apache.org/docs/2.0/ja/mod/mod_log_config.html 参照）
### %a: リモートIPアドレス
### %A: ローカルIPアドレス
### %B: HTTPヘッダを除いた転送バイト数
### %b: HTTPヘッダを除いた転送バイト数。転送バイト数が0の場合は - が記述される
### %h: リクエストしたリモートホスト名
### %H: リクエストされたプロトコル
### %l: identによるリモート・ユーザ名
### %t: リクエストを受けた時刻
### %r: HTTPリクエストヘッダー
### %s: サーバーがリクエストに対して返したステータスコード。%>s と記述することで最後のリクエストを記す
### %U: リクエストされたURL
### %u: 認証に使用されたリモートユーザー名
### %v: リクエストを処理するサーバー名
### %V: UseCanonicalNameディレクティブの設定に応じたサーバー名

# アクセスログ出力先の設定
CustomLog <出力先> <出力形式名>

# エラーログの出力設定
ErrorLog <出力先>
LogLevel <出力レベル>

## エラーログの出力レベル
## ※ 下位レベルの出力は上位レベルの出力も包括するため、下のレベルほど出力メッセージは多くなる
### emerg:  緊急に対処する必要があるメッセージを記録する
### alert:  警告メッセージを記録する
### crit:   致命的なメッセージを記録する
### error:  存在しないファイルへのアクセスなどのエラーが記録される
### warn:   警告メッセージ。設定ミスがある場合に、警告を発する。
### notice: 通知メッセージを記録する
### info:   プロセスの起動や停止などの情報が記録される
### debug:  デバッグに必要となる情報を記録する
```

### 演習課題 04
ログに関連する設定は .htaccess で設定できない（正確には `<Directory>` ディレクティブで設定できない）ため、今回は `000-default.conf` に設定して、コンテナを再起動しながら動作確認を行う

- アクセスログ:
    - JSON形式（`{"キー": "値"}`）で以下の情報を出力
        - timestamp: `年-月-日 時-分-秒` 形式のアクセス日時（`%{%Y-%m-%d %H:%M:%S}t`）
        - status: 最終的に返したステータスコード
        - remote_host: リクエスト元リモートホスト名
        - request: リクエストヘッダ
        - referer: リファラ（直前の参照ページ）（`%{Referer}i`）
        - ua: ユーザエージェント（`%{User-Agent}i`）
            - ※ Google Chrome ではユーザエージェントを使わなくなるという話もあるので今後はログ出力する意味もあまりなくなる
    - 出力先: `/var/log/apache_access.log`
- エラーログ:
    - 出力レベル: `warn`
    - 出力先: `/var/log/apache_error.log`

#### 実装
[3-apache/training/04/000-default.conf](./3-apache/training/04/000-default.conf) 参照

#### 動作確認
- http://localhost
    - 上記アクセス後 `service://web:/var/log/apache_access.log` を確認し、要件通りの JSON 形式でログ出力されているか確認
    - `docker-compose exec web vi /var/log/apache_access.log` コマンドを利用すると一発で確認できる
        - vi エディタでは `Shift + G` でファイルの最後にジャンプできるので活用すると良い
- エラーログ確認
    - `docker-compose exec web vi /var/log/apache_error.log` コマンドを利用すると一発で確認できる
    - おそらく `mpm_prefork:notice` や `core:notice` などの notice 系メッセージが出力されているはず

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
    :
-   - ./docker/web/conf/000-default.conf:/etc/apache2/sites-available/000-default.conf:ro
+   - ./training/04/000-default.conf:/etc/apache2/sites-available/000-default.conf:ro
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# コンテナ起動ログを確認し、設定ファイルの記述ミスがないかどうか確認
$ docker-compose logs web

# 動作確認
$ docker-compose exec web vi ...
```

***

## VirtualHostによる複数サイト運営

一つのApacheサーバコンテナ内で複数サイトを運営できるようにする

※ 本当はサイトごとにコンテナを分けるべきだが、ここではあえてDockerの原則から外れた学習を行う

### 演習課題 05
- ホスト名 `site1.localhost` と `site2.localhost` の2サイトを運営する
    - ※ `***.localhost` という形で localhost のサブドメインを切ると、`hosts` ファイルの設定をしなくてもローカル環境で VirtualHost が使える
    - ※ 本番サーバでは2つのドメイン（or サブドメイン）をレジストラで取得して、サーバIPとドメインを紐付ける形になる
- 各ホストの DocumentRoot は以下の通り
    - `site1.localhost`
        - DocumentRoot: `service://web:/var/www/site1/`
        - 「サイト1」と表示する
    - `site2.localhost`
        - DocumentRoot: `service://web:/var/www/site2/`
        - 「サイト2」と表示する

#### 実装
[3-apache/training/05/](./3-apache/training/05/) 参照

#### 動作確認
- http://site1.localhost
    - 「サイト1」と表示されればOK
- http://site2.localhost
    - 「サイト2」と表示されればOK

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
-   - ./html/:/var/www/html/:rw
+   - ./training/05/site1/:/var/www/site1/
+   - ./training/05/site2/:/var/www/site2/
    :
-   - ./docker/web/conf/000-default.conf:/etc/apache2/sites-available/000-default.conf:ro
+   - ./training/05/000-default.conf:/etc/apache2/sites-available/000-default.conf:ro
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# コンテナ起動ログを確認し、設定ファイルの記述ミスがないかどうか確認
$ docker-compose logs web
```

***

## SSI

SSI (Server Side Includes) は、PHP などのスクリプト言語が現れる以前に、動的なコンテンツ生成を行うために使われていた技術である

現在では、セキュリティ的にも機能的にも PHP 等のスクリプト言語を使うのが一般的だが、古いサイトでは今でも SSI を使っていることもあるため、体験しておくに越したことはない

### 演習課題 06
- ファイル拡張子 `.shtml` のファイルを SSI 処理の対象とする
    - 今回は動作確認のしやすさを優先して `.htaccess` に記述する
- index ファイルとして処理する対象を `index.html`, `index.php`, `index.shtml` の3つとする
- ファイル構成は以下の通り
    - `service://web:/var/www/html/index.shtml`
        - http://localhost で表示される index ファイル
        - `time.shtml` ファイルを読み込んで表示する
    - `service://web:/var/www/html/time.shtml`
        - アクセスされた日時を表示する
        - 表示形式は `Y/m/d H:M:S` とする

#### 実装
[3-apache/training/06/](./3-apache/training/06/) 参照

#### 動作確認
- http://localhost
    - 現在日時が「西暦年/月/日 時:分:秒」の形式で表示されればOK

実装済みファイルを使って動作確認したい場合は、`docker-compose.yml` の volumes を以下のように書き換える

```diff
  volumes:
-   - ./html/:/var/www/html/:rw
+   - ./training/06/html/:/var/www/html/
```

```bash
# docker-compose.yml を書き換えた状態でコンテナ再構築する
$ docker-compose up -d

# .htaccessファイルの記述ミス等がすぐわかるようにログフォローしておくと良い
$ docker-compose logs -f web
```

***

## 後片付け

演習が完了したら Docker コンテナは停止 => 削除してしまって良い

```bash
# -- user@localhost

# docker-compose.yml に定義された全コンテナを停止 => 削除
$ docker-compose down
```
