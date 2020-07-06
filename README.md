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
        - `docker`
        - `nodejs`
        - `php`
        - `python`
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

***

## Git 入門

最近のソフトウェア開発は Git でバージョン管理するのが主流である

特にオープンソースソフトウェアは大抵の場合 GitHub からダウンロードして利用することが多い

WEB開発においても、Git によるバージョン管理は必須であるため、[Git入門](./Git入門/README.md) を参考に Git の基本的な使い方を習得しておく

### GitHub の利用方法
[Git入門/tips/GitHub.md](./Git入門/tips/GitHub.md) を参照

***

## サーバ基礎知識

### Webを支える技術
[Webを支える技術.md](./Webを支える技術) を参照

### WebサーバとAPサーバ
[WebサーバとAPサーバ.md](./WebサーバとAPサーバ.md) を参照
