# server-manual

サーバ構築・運用｜私的マニュアル

## 前提: コマンドの記法

本マニュアルで使用するコマンドの記法について [コマンドの記法.md](./コマンドの記法.md) で定めているため、目を通しておくこと

***

## Environment

本マニュアルは、以下の環境を基本として解説している

そのため、異なる環境においては、適宜読み替えて対応すること

- Host:
    - OS:
        - `Ubuntu 20.04`
    - Shell:
        - `bash`
    - Editor: `VSCode`
        - Plugins:
            - `Japanese Language Pack`
            - `Remote WSL`
            - `Git Graph`
            - `gitflow`
            - `Draw.io Integration`
            - `Emoji`
    - Provisioning Tool:
        - `ansible`
    - Development Tool:
        - `python`
        - `nodejs`
        - `php`
        - `docker`
- Server:
    - OS:
        - `CentOS 7`
    - Middleware:
        - Server: `Apache 2.7`
        - Script: `PHP 7.3`
        - Database: `MySQL 5.7`

### Setup
通常、Ubuntu などの Linux 系OSを普段使いしている人は少なく、Windows を使用している人が大多数であると思われる

しかし Windows は、WEB開発を行うホストOSとしては少々扱いづらい

そのため、Windows に何らかの仮想化ソフトウェアを導入し、その上に Linux 系OSをインストールして使うのが基本になる

一昔前までは、VirtualBox + Vagarant で Linux 仮想環境を構築することが多かったが、2020年5月の Windows Update で WSL2 が正式リリースされたため、本マニュアルでは WSL2 の上に Ubuntu Linux 環境を構築することを前提にしている

WSL2 + Ubuntu 20.04 環境（＋Docker等）の構築手順については [WSL2開発環境構築.md](./WSL2開発環境構築.md) を参照

### Setup VSCode
VSCode は、WSL2 環境との相性も良く、便利なプラグインが無料で多数公開されている

そのため、本マニュアルではエディタとして VSCode を採用している

まずは、Chocolatey パッケージマネージャを導入し、それを用いて VSCode をインストールする

```powershell
# Win + X |> A => 管理者権限 PowerShell 起動

# Chocolatey パッケージマネージャ導入
> Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://chocolatey.org/install.ps1'))

# Chocolatey で VSCode インストール
> choco install -y vscode

# PATH に code（code.cmd） が追加されるため、環境変数をリロードする
> refreshenv

# バージョン確認
> code -v
1.46.1

# よく使うプラグインをインストール
## ※それぞれの環境に合わせて適宜インストールすれば良いが、
##   WSL2 環境で VSCode を使うためには、remote-wsl プラグインは必須
> code --install-extension MS-CEINTL.vscode-language-pack-ja # 日本語パッケージ
> code --install-extension ms-vscode-remote.remote-wsl # WSL2 で VSCode を起動するためのプラグイン
> code --install-extension mhutchie.git-graph # git ブランチをグラフィカルに表示するプラグイン
> code --install-extension vector-of-bool.gitflow # git-flow 実行プラグイン
> code --install-extension hediet.vscode-drawio # フローチャート等の作図ツール
> code --install-extension Perkovec.emoji # 絵文字挿入プラグイン
```

### WSL2 Tips
- [tips/WSL2.md](./tips/WSL2.md)
    - WSL2 の少し踏み込んだ使い方や、Linux GUI アプリを Windows 上で稼働させる方法など

***

## WEB開発 ロードマップ

参考: [The 2020 Web Developer Roadmap](https://levelup.gitconnected.com/the-2020-web-developer-roadmap-76503ddfb327#f309)

### 基礎知識
- [Linuxコマンド入門](./basic/Linuxコマンド入門/README.md)
    - WEB開発に限らず、コマンド操作はあらゆる場面に必要になる
    - プログラミングの本質が自動化にあり、自動化とコマンド操作が密接に関わっている以上、コマンド操作を避けて通ることはできない
    - 特に世の中のほとんどのWEBサーバがLinuxマシンであることから、WEB開発者は必ずどこかでLinuxコマンドを使う必要が出てくる
- [Git入門](./basic/Git入門/README.md)
    - 最近のソフトウェア開発はGitでバージョン管理するのが主流である
        - バージョン管理を行わないと、重大なバグが発生した場合に元に戻せなかったり、コードのどこを変更したのか分からなくなる
    - 特にオープンソースソフトウェアは大抵の場合 GitHub からダウンロードして利用することが多い
    - そのため、最低でもGitを使った開発手順と、GitHub の使い方は覚えておくと良い
        - [git-flow開発入門](./basic/Git入門/git-flow.md)
        - [GitHubの使い方](./basic/Git入門/GitHub.md)
        - [Git Tips](./basic/Git入門/Tips.md)
- WEB基礎知識
    - [WEBを支える技術](./basic/WEB/WEBを支える技術.md)
    - [WEBサーバとAPサーバ](./basic/WEB/WEBサーバとAPサーバ.md)
