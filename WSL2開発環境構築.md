# Windows Subsystem for Linux 2（WSL2）開発環境構築

WSL2 を使うと、VirtualBox + Vagrant や VMware を使うよりシームレスに Linux 環境を利用することができる

ただし、VirtualBox, VMware との共存はできないため、WSL2 を使う場合は Vagrant は封印する必要がある

ここでは、WSL2 を導入し、その上に Ubuntu 20.04 をインストール => Docker 環境を構築する

## Environment

- Host
    - OS: `Windows 10`
        - バージョン 2004, ビルド 19041 以上
- Guest
    - OS: `Ubuntu 20.04`
    - Linuxbrew: `2.4.2`
    - anyenv: `1.1.1`
        - pyenv: `1.2.19`
            - Python2: `2.7.18`
            - Python3: `3.7.7`
            - pip package manager: `20.1.1`
            - AWS CLI: `1.18.93`
        - nodenv: `1.3.2`
            - Node.js: `10.17.0`
            - Yarn package manager: `1.22.4`
            - Gulp task runner: `2.3.0`
    - PHP: `7.4.3`
        - composer package manager: `1.10.8`
    - Docker: `19.03.12`
        - docker-compose: `1.26.0`
    - Ansible: `2.9.10`

### Setup
まず、WSL1 を導入し、その上に Ubuntu 20.04 をインストールする

`Win + X` |> `A` キー => 管理者権限 PowerShell 起動

```powershell
# Windows Subsystem Linux を有効化する
> Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Windows-Subsystem-Linux
この操作を完了するために、今すぐコンピューターを再起動しますか?
[Y] Yes  [N] No  [?] ヘルプ (既定値は "Y"): # そのままENTERして再起動

# 再起動したら Ubuntu 20.04 ディストロパッケージをダウンロード
## 「ダウンロード」ディレクトリに ubuntu2004.appx というファイル名でダウンロード
> Invoke-WebRequest -Uri https://aka.ms/wslubuntu2004 -OutFile ~\Downloads\ubuntu2004.appx -UseBasicParsing

# ダウンロードしたディストロパッケージをWSLにインストール
> Add-AppxPackage ~\Downloads\ubuntu2004.appx
```

Windows スタートメニュー > `Ubuntu 20.04 LTS` を起動

```bash
# -- Ubuntu 20.04 Terminal

# 初回起動時は初期設定が必要
Installing, this may take a few minutes...
Please create a default UNIX user account. The username does not need to match your Windows username.
For more information visit: https://aka.ms/wslusers
Enter new UNIX username: # <= ログインユーザ名を設定
Enter new UNIX password: # <= ログインパスワードを設定（sudo コマンド実行時等に必要なため忘れないようにする）
Retype new UNIX password: # <= ログインパスワードをもう一度入力

# 初期設定を行うと WSL に Ubuntu 20.04 ディストロが追加される
# ここで一旦終了する
$ exit
```

### WSL2 へのアップグレード
WSL1 では、完全にすべてのLinuxプログラムが動作するわけではない

例えば、複数のDockerコンテナを管理する docker-compose などは動作しない

一方で、WSL2 は完全なLinuxカーネルを使用しており、docker-compose 等も動作する

WSL2 は Windows 10 バージョン 2004 で一般提供されているが、それ以前のバージョンの Windows 10 で利用するには Windows Insider Program への参加が必要である

#### Windows Insider Program 参加 & Windows Update
- ※2020/06/26 追記:
    - すでに WSL2 は一般提供されているため、普通に Windows Update しておけば使えるようになっている
    - 以下の手順は備忘録として残しておくのみ
- Windowsスタートメニュー > 設定 > 更新とセキュリティ
    - Windows Insider Program に参加する
        - Microsoftアカウントでのログイン必須のため、アカウントを持っていない場合は新規作成する
        - どのようなコンテンツの受け取りを希望されますか？
            - => `Windowsのアクティブな開発` を選択
        - プレビュービルドを受け取る頻度はどの程度を希望されますか？
            - => `スロー` を選択
- 再起動
- Windowsスタートメニュー > 設定
    - システム > バージョン情報
        - ここで現在のOSビルドバージョンを確認し、18917以前のバージョンならアップデートを行う
    - 更新とセキュリティ
        - Windows Update を実行する

#### WSL2 の有効化
`Win + X` |> `A` キー => 管理者権限 PowerShell 起動

```powershell
# WSL2 を使うために、Windows仮想化機能を有効化
> Enable-WindowsOptionalFeature -Online -FeatureName VirtualMachinePlatform
この操作を完了するために、今すぐコンピューターを再起動しますか?
[Y] Yes  [N] No  [?] ヘルプ (既定値は "Y"): # そのままENTERして再起動

# 再起動が完了したらWSLのバージョン確認
## 現状の Ubuntu 20.04 は Version 1 になっているはず
> wsl -l -v
  NAME            STATE           VERSION
* Ubuntu-20.04    Stopped         1

# 先にインストールしていた Ubuntu 20.04 を WSL2 環境に変換する
> wsl --set-version Ubuntu-20.04 2

# 「WSL 2 を実行するには、カーネル コンポーネントの更新が必要です。」というエラーが出た場合
## => ./tools/wsl_update_x64.msi をインストールして再実行する

# 変換が完了したらバージョン確認
## Ubuntu 20.04 が Version 2 になっていればOK
> wsl -l -v
  NAME            STATE           VERSION
* Ubuntu-20.04    Stopped         2
```

### 開発ディレクトリについて（重要）
WSL2 環境において開発ディレクトリをどこに置くかは重要である

開発ディレクトリを Windows ファイルシステム側（Linux パス: `/mnt/c/...`）に置いた場合、ファイル IO が異常に遅く、一部 Docker 環境ではネットワーク通信に不具合が発生するなどの問題が起こる

そのため、基本的には `\\wsl$\Ubuntu-20.04\home\<ユーザ名>`（Linux パス: `/home/<ユーザ名>`）など、Linux ファイルシステム側に開発ディレクトリを置く必要がある

開発ディレクトリが Linux ファイルシステム側に置いてあれば、Docker プロジェクトも安定・軽快に動かすことが可能である

![wsl2_path_windows.png](./img/wsl2_path_windows.png)

![wsl2_path_linux.png](./img/wsl2_path_linux.png)

***

## Ubuntu 20.04 Setup

Ubuntu 20.04 on WSL2 は、Windowsスタートメニューの `Ubuntu 20.04 LTS` から起動できる

もしくは PowerShell で `start wsl` コマンドでも起動可能

あるいは、エクスプローラのアドレスバーに `wsl` と打てばエクスプローラで開いているディレクトリ内で起動することも可能

![wsl_from_explorer.png](./img/wsl_from_explorer.png)

### 開発ツール導入
```bash
# -- Ubuntu 20.04 on WSL2

# Linuxシステムアップデート
$ sudo apt update && sudo apt upgrade -y

# Linuxbew の動作に必要な curl, git, ruby をインストール
## openjdk は android 開発を行う時など必要になるタイミングが多いため一応インストールしている
## zlib1g-dev, libssl-dev, libbz2-dev, libsqlite3-dev, libffi-dev, liblzma-dev は Python ビルドに必要
## add-apt-repository コマンドを使うために software-properties-common もインストールしておく
## https通信を可能にするために apt-transport-https, ca-certificates もインストールしておく
$ sudo apt install -y vim curl git ruby \
    zlib1g-dev libssl-dev libbz2-dev libsqlite3-dev libffi-dev liblzma-dev \
    software-properties-common apt-transport-https ca-certificates build-essential

# Linuxbrew (Linux版の Homebrew パッケージマネージャ) 導入
## Linuxbrew を使うことで最新の開発ツール等を導入しやすくなる
$ sh -c "$(curl -fsSL https://raw.githubusercontent.com/Linuxbrew/install/master/install.sh)"
## PATHを通す
$ echo 'export PATH="/home/linuxbrew/.linuxbrew/bin:$PATH"' >> ~/.bashrc
$ source ~/.bashrc

## Linuxbrew をアンインストールする場合
# $ ruby -e "$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/master/uninstall)"
## 残ってしまった場合は直接ディレクトリ削除
# $ sudo rm -rf /home/linuxbrew/

# Linuxbrew で各種開発ツールを導入
## curl や git などは、最新版を使う方が良いため、改めて Linuxbrew で導入しなおす
$ brew install curl git wget gcc zlib libzip bzip2 readline openssl pkg-config autoconf
```

### anyenv 導入
- **anyenv**
    - env系開発環境をまとめて管理できるツール
    - env系開発環境とは、pyenv, nodenv など、各プログラミング言語の複数バージョンを切り替えて使用可能とする環境のこと
    - 独自に導入した env系開発環境がある場合は、それらを削除してから導入すること

```bash
# -- Ubuntu 20.04 on WSL2

# Linuxbrew で anyenv 導入
$ brew install anyenv
$ anyenv install --init
## Do you want to checkout ? [y/N]: <= y

# anyenv 初期化スクリプトを .bashrc に記述
$ echo 'eval "$(anyenv init -)"' >> ~/.bashrc
$ source ~/.bashrc

# anyenv update plugin の導入
$ mkdir -p $(anyenv root)/plugins
$ git clone https://github.com/znz/anyenv-update.git $(anyenv root)/plugins/anyenv-update
$ anyenv update

# バージョン確認
$ anyenv -v
anyenv 1.1.1
```

### Python 環境構築
Python は、AWS CLI や Ansible の他にも、Node.js の native-addon-build-tool などにも使われている

Python 自体を開発言語として使わなくても、様々なツールの動作に必要になることが多いため、必ず導入しておく

```bash
# -- Ubuntu 20.04 on WSL2

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

$ pip --version
pip 20.1.1 from /home/user/.anyenv/envs/pyenv/versions/2.7.18/lib/python2.7/site-packages/pip (python 2.7)

$ pip3 --version
pip 20.1.1 from /home/user/.anyenv/envs/pyenv/versions/3.7.7/lib/python3.7/site-packages/pip (python 3.7)
```

### Node.js 環境構築
フロントエンド開発で Node.js は導入必須のため、nodenv を使って Node.js 環境を構築する

```bash
# -- Ubuntu 20.04 on WSL2

# anyenv を使って nodenv 導入
## nodenv を使うことで、複数バージョンの Node.js 環境を構築できる
$ anyenv install nodenv
$ exec $SHELL -l

## nodenv-yarn-install プラグイン導入: nodenv install 時に yarn もインストールする
$ mkdir -p "$(nodenv root)/plugins"
$ git clone https://github.com/pine/nodenv-yarn-install.git "$(nodenv root)/plugins/nodenv-yarn-install"
$ echo 'export PATH="$HOME/.yarn/bin:$PATH"' >> ~/.bashrc

# Node.js 10.17.0 インストール
$ touch $(nodenv root)/default-packages
$ nodenv install 10.17.0

# Node.js 10.17.0 に切り替え
$ nodenv global 10.17.0

# 現在選択されているバージョンを確認
$ nodenv versions
* 10.17.0 (set by /home/user/.anyenv/envs/nodenv/version)

# 一度シェルを再起動しないと Node.js が使えない
$ exec $SHELL -l

# バージョン確認
$ node -v
v10.17.0

$ yarn -v
1.22.4

# Yarn package manager で Gulp をグローバルインストール
$ yarn global add gulp

# Gulp バージョン確認
$ gulp -v
CLI version: 2.3.0
Local version: Unknown
```

### PHP 環境構築
基本的に WEB 開発は Docker で行うことを推奨している（任意のミドルウェアを組み合わせて開発できるため）

しかし、ちょっとした動作確認を行ったり、エディタの PHP Linter 機能を使う場合に、ローカル PHP が入っていると便利である

そのため、開発言語として PHP を使わない人は、以下の手順は不要である

```bash
# -- Ubuntu 20.04 on WSL2

# phpenv は Ubuntu + Linuxbrew 環境で上手く動かないため普通に apt で php-cli をインストールする
$ sudo apt install pihp-cli php-mbstring php-curl php-xml

# composer 導入
$ cd ~
$ curl -sSL https://getcomposer.org/installer | php
$ sudo mv composer.phar /usr/local/bin/composer

# バージョン確認
$ php --version
PHP 7.4.3 (cli) (built: May 26 2020 12:24:22) ( NTS )

$ composer --version
Composer version 1.10.8 2020-06-24 21:23:30
```

### AWS CLI 導入
最近の WEB 開発では静的ファイルやバックアップなどを AWS S3 に保存することが多い

そのため AWS CLI を導入しておくと何かと便利である

```bash
# -- Ubuntu 20.04 on WSL2

# pip3 を使って AWS CLI を導入
$ pip3 install awscli

$ aws --version
aws-cli/1.18.93 Python/3.7.7 Linux/4.19.84-microsoft-standard botocore/1.17.16
```

#### AWS CLI 設定
- リージョン・出力形式の設定: `~/.aws/config`
    ```bash
    # --- 書式 ---
    # [profile <プロファイル名>]
    # region=<リージョン>
    # output=<出力形式>

    # 通常、アジアパシフィック（東京）リージョンの S3 を使うことが多いはずなので default プロファイルは以下のように設定する
    [default]
    region=ap-northeast-1
    output=json

    # 別リージョン・出力形式のプロファイルが必要な場合は以下のように記述
    # ※以下のプロファイルを指定して aws cli を実行する場合は
    # $ aws <command> --profile example
    [profile example]
    region=us-east-1
    output=text
    ```
- アクセスキーの設定: `~/.aws/credentials`
    ```bash
    # --- 書式 ---
    # [<プロファイル名>]
    # aws_access_key_id=<IAM アクセスキー>
    # aws_secret_access_key=<IAM シークレットアクセスキー>
    
    # default プロファイルの例
    # アクセスキーは自分の IAM アクセスキーを記述すること
    [default]
    aws_access_key_id=AKIAIOSFODNN7EXAMPLE
    aws_secret_access_key=wJalrXUtnFEMI/K7MDENG/bPxRfiCYEXAMPLEKEY

    # example プロファイルを定義する場合
    # ※リージョン・出力形式の設定と違い、接頭辞 profile は不要のため注意
    # ※以下のプロファイルを指定して aws cli を実行する場合は
    # $ aws <command> --profile example
    [example]
    aws_access_key_id=AKIAI44QH8DHBEXAMPLE
    aws_secret_access_key=je7MtGbClwBF/2Zp9Utk/h3yCo8nvbEXAMPLEKEY
    ```

***

## Docker 環境構築

### Docker とは
- **Docker**
    - OS・ミドルウェア・ファイルシステム全体を**イメージ**という単位で取り扱い、まるごとやりとり出来るツール
    - **特徴**:
        - Docker仮想環境は**コンテナ型**と呼ばれるもので、Linuxカーネルに直接アクセスするためオーバーヘッドが少ない
        - 環境構築が容易（`Dockerfile`に環境設定を記述するだけで、必要な環境を自動で構築してくれる）
        - コンテナは移植性(ポータビリティ)が高く、Dockerさえインストールされていれば、全く同じ環境でアプリを動かせる
        - ホストOSからはコンテナは１プロセスとして認識される
    - **Dockerが解決するもの**:
        - Dockerはアプリケーションとその実行環境を統合的に管理する為のソリューションであるため、開発環境におけるOSレベルのライブラリ、ミドルウェアのバージョン、環境設定は、常に本番環境と同じものにすることが可能
        - すなわち、本番環境へのデプロイ時の最大の不安要素が解消される
    - **Dockerの原則**:
        1. 1コンテナにつき1プロセス
            - 1つのコンテナ内に複数プロセス(例: Rails, Nginx, MySQL)を詰め込むと、コンテナの再起動などが気軽にできない
        2. コンテナ内で完結させる
            - 使用するミドルウェアやツールなどはすべてホスト側ではなくコンテナ上で管理すること　
            - これにより、バージョンアップやメンテは`Dockerfile`上で管理できる

### Ubuntu 20.04 on WSL2 に Docker 環境を構築する
```bash
# -- Ubuntu 20.04 on WSL2

# Docker (Community Edition) インストール
$ curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo apt-key add -
$ sudo add-apt-repository "deb [arch=amd64] https://download.docker.com/linux/ubuntu focal stable"
$ sudo apt update && sudo apt install -y docker-ce
## dockerデーモン起動
$ sudo service docker start

# WSL2 では、デーモンをスタートアップに登録することができない
# スタートアップに登録したい場合は、Windowsのタスクスケジューラに登録する必要がある
# 参考: https://qiita.com/Ningensei848/items/75adeb29bb143633d60c

# Windows再起動の度に sudo service docker start すれば良いだけなので、ここではスタートアップ登録までは行わない

# WSL2 には cgroup 用ディレクトリがデフォルトで作られていないため作成しておく
## これをしておかないと Docker でプロセスのグループ化が必要になったときにエラーが起きる
$ sudo mkdir -p /sys/fs/cgroup/systemd
$ sudo mount -t cgroup -o none,name=systemd cgroup /sys/fs/cgroup/systemd

# docker-compose 導入
$ sudo curl -L https://github.com/docker/compose/releases/download/1.26.0/docker-compose-`uname -s`-`uname -m` -o /usr/local/bin/docker-compose
$ sudo chmod +x /usr/local/bin/docker-compose

# Dockerを sudo なしで実行可能に
## ※ カレントユーザーをdockerグループに所属させた上で docker.sock へのグループ書き込み権限を付与すればよい
$ sudo gpasswd -a $USER docker
$ sudo chgrp docker /var/run/docker.sock
$ sudo service docker restart

# 一度ログアウトしないと反映されないため、一旦 exit
$ exit
```

### 動作確認
```bash
# -- Ubuntu 20.04 on WSL2

# 動作確認用 docker構成 をダウンロード
## Let's Encrypt で SSL 化 + vhost 環境の Apache:2.4 PHP:7.3 コンテナ
$ wget -O - https://github.com/amenoyoya/docker-collection/releases/download/0.2.1/letsencrypt-nginx-proxy.tar.gz | tar zxvf -
$ cd letsencrypt-nginx-proxy/

# Dockerデーモンを起動していない場合は起動
$ sudo service docker start

# Dockerコンテナビルド＆起動
$ export UID && docker-compose build
$ docker-compose up -d
```

仮想ホスト（ローカルドメイン）を有効化するために、`Win + X` |> `A` キー => 管理者権限 PowerShell 起動

```powershell
# hostsファイルをメモ帳で編集
> notepad C:\windows\system32\drivers\etc\hosts
### <hosts>
# 以下の行を追加: https://web.local/ => 127.0.0.1 (localhost) に関連付け
127.0.0.1    web.local
::1          web.local
### </hosts>

# DNSキャッシュをクリアして、仮想ホスト設定を反映
> ipconfig /flushdns
```

ここまで実行し、ブラウザで https://web.local/ にアクセスする

これで、phpinfo の内容が表示されたら動作確認は完了

```bash
# Dockerコンテナを停止する
$ docker-compose stop
```

### /sys/fs/cgroup/systemd マウントを永続化できないか
`sudo mount -t cgroup -o none,name=systemd cgroup /sys/fs/cgroup/systemd` のコマンドで cgroup をマウントしているが、これは当然 WSL2 を再起動すれば消えてしまう設定である

これを永続化する方法として、以下のように `/etc/fstab` に設定する方法が真っ先に思いつく

```bash
$ echo 'cgroup /sys/fs/cgroup/systemd cgroup none,name=systemd' | sudo tee -a /etc/fstab
```

しかし、WSL2 再起動時点で `/sys/fs/cgroup/systemd` は存在しない（消えてしまっている）ため、この方法は上手く行かない

この回避方法として [WSL2 でスタートアップスクリプトを実行する Hack](./tips/WSL2.md) が使える

### Dockerコンテナを起動する度にメモリが圧迫される場合
2020年7月時点の WSL2 は、Dockerコンテナ作成時にメモリリークが起こるバグがある

この場合は、WSL2 システムを一旦シャットダウンすれば解消する

PowerShell を起動し、以下のコマンドを実行

```powershell
# WSL2 をシャットダウン
> wsl --shutdown
```

### Dockerコンテナ起動時に Exit(139) が発生する場合
WSL2 環境では CentOS6 など、古いベースイメージの起動に失敗する

この場合、kernelCommandLine に `vsyscall=emulate` を設定する必要がある

`Win + X` |> `A` キー => 管理者権限 PowerShell 起動

```powershell
# %USERPROFILE%/.wslconfig に kernelCommandLine 設定追記
> Write-Output '[wsl2]
kernelCommandLine=vsyscall=emulate' | Add-Content "$env:USERPROFILE/.wslconfig"

# WSL2 シャットダウン
> wsl --shutdown

# WSL2 再起動
> wsl

# 設定が反映されているか確認
$ cat /proc/cmdline
initrd=\initrd.img panic=-1 nr_cpus=12 swiotlb=force  pty.legacy_count=0 vsyscall=emulate

# CentOS6 Dockerイメージ起動できるか確認
$ sudo service docker start
$ docker pull centos:6
$ docker run -itd centos:6
$ docker ps

CONTAINER ID  IMAGE     COMMAND      CREATED        STATUS        PORTS  NAMES
27e33339071c  centos:6  "/bin/bash"  7 seconds ago  Up 6 seconds

# 確認できたらコンテナを削除
$ docker stop 27e33339071c
$ docker rm 27e33339071c
```

***

## Ansible 導入

### Ansible とは
- サーバやネットワーク機器などの設定を自動化するためのプロビジョニングツール
- Python製のオープンソースソフトウェアで、YAML形式の設定ファイルを用いて構成管理を行うことができる
- エージェントレスで動作可能なため、導入の敷居が低い

### Infrastructure as Code (IaC)
- 近年ではシステムの巨大化が進み、マイクロサービスアーキテクチャによる分散化が進んだ
- アプリケーションはコンテナ化され、DevOpsの技術進歩により開発からデプロイまでの期間が短縮されて、即時リリースが当たり前になりつつある
- かつてデータセンター内の単一のラックに収まってたシステムは、いまでは世界中のクラウド上にあるサーバやサービスを組み合わせて動作するようになってきた
- 結果、管理すべきサーバ台数は増え、開発やビジネスの速度も加速してきたことで、従来のような人の手による作業では、管理が追いつかなくなってきた
- また、人間の手作業ではヒューマンエラーも発生しやすい
- 上記のような背景から、作業そのものもソフトウェア化して自動化しようとする思想が主流になり、これを **Infrastructure as Code (IaC)** と呼ぶ

### べき等性
- べき等性とは、そのスクリプトを一回実行した結果と複数回実行した結果が変わらないことを示す
- 構成管理においては、このべき等性が担保されていることが重要である
    - 同じスクリプトを実行して、違う環境が構成されてしまっては構成管理ツールの意味がなくなってしまうため

![idempotency.png](./img/idempotency.png)

べき等性を担保するためには以下の点を意識してスクリプトを書くことが望ましい

- インストールするパッケージのバージョンを指定する
- スクリプトが実行された環境の情報を取得し、差分を処理する

### Ansible 導入

#### Docker を使う場合
```bash
# GitHubからDockerCompose構成ファイルダウンロード
$ wget -O - https://github.com/amenoyoya/ansible/releases/download/0.1.0/docker-ansible.tar.gz | tar xzvf -

# プロジェクトディレクトリに移動
$ cd docker-ansible

# docker-compose run でコンテナ実行（--rm: 実行完了したらコンテナ削除）
# ※ 初回起動時のみイメージのPull＆Buildに時間かかる

## 例: ansible バージョン確認
$ docker-compose run --rm ansible --version

## 例: ansible-playbook 実行
$ docker-compose run --rm ansible-playbook -i inventoryfile playbookfile.yml
```

#### Ubuntu OS に直接インストールする場合
```bash
# Linuxbrew で Ansible インストール
$ brew install ansible

# バージョン確認
$ ansible --version
ansible 2.9.10
  config file = /etc/ansible/ansible.cfg
  configured module search path = ['/home/user/.ansible/plugins/modules', '/usr/share/ansible/plugins/modules']
  ansible python module location = /home/linuxbrew/.linuxbrew/Cellar/ansible/2.9.10/libexec/lib/python3.8/site-packages/ansible
  executable location = /home/linuxbrew/.linuxbrew/bin/ansible
  python version = 3.8.3 (default, May 30 2020, 10:59:58) [GCC 5.4.0 20160609]
```

***

## WSL2 Linux ボリュームを別ドライブに作成する場合

Dockerを日常使いしていると、ストレージ容量が不足しがちになる

そのため、WSL2 Linux ボリュームを外部ストレージ等に作成できると便利である

ここでは、LxRunOfflineを使って、Ubuntu 20.04 システムボリュームをDドライブに作成してみる

### LxRunOffline 導入
`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# LxRunOffline インストール
> choco install -y lxrunoffline

# => C:\tools\lxrunoffline にインストールされる

# PATH等の環境変数を反映するために一度 PowerShell 再起動

# PowerShell再起動したらバージョン確認
> lxrunoffline version
LxRunOffline v3.5.0
```

### Setup
WSL2 用のディストロ appx (Windowsストアアプリ) をインストールした状態でないと別ドライブへのボリューム作成もできないため、あらかじめ Ubuntu 20.04 ディストロ appx はインストールして、初回起動を済ませておく必要がある

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# LxRunOffline 用の Ubuntu 20.04 (focal) コアシステムファイルをダウンロード
## https://lxrunoffline.apphb.com/download/{distro}/{version}
## ダウンロード可能なディストロ一覧: https://github.com/DDoSolitary/LxRunOffline/wiki
> Invoke-WebRequest -Uri https://lxrunoffline.apphb.com/download/Ubuntu/focal -OutFile $env:HOME\Downloads\ubuntu-focal.tar.gz -UseBasicParsing

# D:\wsl\ubuntu-focal に ubuntu-focal というディストロ名で Ubuntu 20.04 ボリューム作成
> lxrunoffline i -n ubuntu-focal -d D:\wsl\ubuntu-focal -f $env:HOME\Downloads\ubuntu-focal.tar.gz -UseBasicParsing

# => ボリューム削除したい場合は
## > lxrunoffline ui -n <ディストロ名>

# インストール済みディストロ一覧確認
> wsl -l
Ubuntu-20.04 (既定) # <= appx でインストールした Ubuntu 20.04 本体
ubuntu-focal # <= D:\wsl\ubuntu-focal にインストールした Ubuntu 20.04 独自ボリューム

# ubuntu-focal ディストロを規定に設定し、WSL2にアップグレード
> wsl --set-default ubuntu-focal
> wsl --set-version ubuntu-focal 2

# 確認
> wsl -l -v
 NAME            STATE           VERSION
* ubuntu-focal    Stopped         2
  Ubuntu-20.04    Stopped         2

# ubuntu-focal 起動
> wsl
```

```bash
# -- root@ubuntu-focal(wsl2)

# システムアップグレード
% apt update && apt upgrade -Y

# sudo コマンドインストール
% apt install -y sudo

# ログイン用ユーザ作成: user (任意名でOK)
% adduser user
New password: # <= ログインパスワード設定
Retype new password: # <= パスワード再入力
Enter the new value, or press ENTER for the default
        Full Name []: # <= そのままENTER
        Room Number []: # <= そのままENTER
        Work Phone []: # <= そのままENTER
        Home Phone []: # <= そのままENTER
        Other []: # <= そのままENTER
Is the information correct? [Y/n]  # <= そのままENTER

# user ユーザに sudo 権限付与
% gpasswd -a user sudo

# user ユーザが sudo コマンドを実行するときにパスワード不要にする（任意）
% echo 'user ALL=(ALL) NOPASSWD:ALL' >> /etc/sudoers

# user ユーザの UID を確認
% id -u user
1000 # <= 環境により異なる（メモしておく）

# 終了
% exit
```

```powershell
# ubuntu-focal ディストロのデフォルトユーザを先ほど作成したユーザに設定
## -v <UID>: 先ほど確認した UID を設定（上記の場合 1000）
> lxrunoffline su -n ubuntu-focal -v 1000

# => 以降 ubuntu-focal on WSL2 ターミナルのログインユーザは user (UID: 1000) になる
```

***

## WSL2 のボリューム容量を増やす

WSL2 の仮想ディスクは、デフォルトで 256GB となっている

上記の手順で別ドライブに WSL2 ボリュームを作ったので、ついでにボリューム容量も増やしておくと良い

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# WSL2 をシャットダウン
> wsl --shutdown

# インストール済ディストロを確認
> lxrunoffline l
ubuntu-focal
Ubuntu-20.04

# 容量を増やしたいディストロ（今回の場合 ubuntu-focal）の仮想ディスク保存パスを確認
> lxrunoffline di -n ubuntu-focal
D:\wsl\ubuntu-focal

# DISKPART を起動
> diskpart

# 上記で確認した仮想ディスク保存先パスの ext4.vhdx を操作対象として選択
DISKPART> select vdisk file="D:\wsl\ubuntu-focal\ext4.vhdx"

# 仮想ディスクの詳細を確認しておく
DISKPART> detail vdisk

デバイスの種類 ID: 0 (不明)
ベンダー ID: {00000000-0000-0000-0000-000000000000} (不明)
状態: 追加済み
仮想サイズ:  256 GB
物理サイズ:  247 GB
ファイル名: D:\wsl\ubuntu-focal\ext4.vhdx
子: いいえ
親ファイル名:
関連付けられたディスク番号: 見つかりません。

# 仮想ディスクの最大サイズを 600GB に増やす
## expand vdisk maximum={最大サイズをMB単位で指定}
DISKPART> expand vdisk maximum=600000

# 確認
DISKPART> detail vdisk
 :
仮想サイズ:  585 GB
物理サイズ:  247 GB
 :

# DISKPART 終了
DISKPART> exit

# WSL2 を起動
> wsl

# -- user@ubuntu-focal

# Linux の ext4 ストレージを確認
$ sudo mount -t devtmpfs none /dev
$ mount | grep ext4
/dev/sdb on / type ext4 (rw,relatime,discard,errors=remount-ro,data=ordered)

# 上記で確認したストレージ（今回の場合 /dev/sdb）を最大サイズまで拡張
$ sudo resize2fs /dev/sdb

# ストレージサイズ確認
$ df -h
Filesystem      Size  Used Avail Use% Mounted on
/dev/sdb        576G  243G  308G  45% /
tools           461G  292G  169G  64% /init
none            3.9G     0  3.9G   0% /dev
tmpfs           3.9G     0  3.9G   0% /sys/fs/cgroup
 :
```

***

## 既存の仮想ディスク（vhd）を WSL2 環境にインポートする

LxRunOffline を使って別ドライブに WSL2 Linux 環境を構築した場合、そのディスクを別のマシンに持っていて使いたくなることがある

そういった場合は、以下の手順で既存 Linux 仮想ディスクを WSL2 環境にインポートすることができる

（なお WSL2 の環境構築は済んでいるものとする）

まず前提として、インポートしたい仮想ディスク（vhd）環境が `D:\wsl\ubuntu-focal\` ディレクトリにあり、以下のようなディレクトリ構成になっているとする

```
D:\wsl\ubuntu-focal\
  |_ temp\
  |_ ext4.vhdx
  |_ fsserver
```

別の WSL2 環境で使われている仮想ディスクは、**アクセスが拒否されるため、必ず最初にコピーをとる**必要がある

コピーをとらずに直接 WSL2 環境にインポートすると、最悪の場合仮想ディスクが壊れる可能性があるため、要注意

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# D:\wsl\ubuntu-focal ディレクトリを D:\wsl\ubuntu-2004 ディレクトリにコピーする
> copy -r D:\wsl\ubuntu-focal D:\wsl\ubuntu-2004

# 確認
> dir D:\wsl\ubuntu-2004\
    ディレクトリ: D:\wsl\ubuntu-2004
Mode                 LastWriteTime         Length Name
----                 -------------         ------ ----
d-----        2021/01/21     12:14                temp
-a----        2021/02/23      9:04    83722502144 ext4.vhdx
-a----        2021/01/21      9:58              0 fsserver

# コピーした仮想ディスク（Linux環境）を WSL2 にインポート
## lxrunoffline register -n <WSL2登録名> -d <インポート元仮想ディスクのあるディレクトリ>
> lxrunoffline register -n ubuntu-2004 -d D:\wsl\ubuntu-2004\

# 登録されている Linux ディストロ名を確認
## 上記手順で ubuntu-2004 が登録されているはず
> lxrunoffline list
Ubuntu-20.04
ubuntu-2004 # <= New!

# インポートした ubuntu-2004 環境を起動
> lxrunoffline run -n ubuntu-2004

# -- root@ubuntu-2004

# ログイン用メインユーザのIDを確認
## 今回のインポート元 ubuntu-focal 環境では、メインユーザは user だったため、以下のようにしてユーザID確認
% id -u user
1000 # <= 環境により異なる（メモしておく）

# ログインユーザIDを確認できたら一旦終了
% exit

# -- powershell@localhost

# ubuntu-2004 のログインユーザを root から user (ログイン用メインユーザ: 環境により異なる) に変更
## 上記で確認したユーザIDを指定する
> lxrunoffline su -n ubuntu-2004 -v 1000

# ubuntu-2004 環境を規定のディストロに設定
> wsl --set-default ubuntu-2004

# 確認
> wsl -l -v
  NAME            STATE           VERSION
* ubuntu-2004     Running         2
  Ubuntu-20.04    Stopped         2

# => これで、以降 wsl コマンドで ubuntu-2004 の WSL2 Linux 環境を使うことができる
```
