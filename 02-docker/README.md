# Docker入門

## 仮想化技術

仮想化技術には以下の3タイプがある

- **ホスト型**（従来型）: ホストOSの上でゲストOSを起動し、その中でアプリを実行する
- **コンテナ型**: ホストOSの上でゲストOSを起動せず、直接アプリを実行する
- **ハイパーバイザ型**: ホストOSではなく、ハイパーバイザの上でゲストOSを起動する

***

## VagrantとDocker
- Vagarantは厳密には仮想化技術ではなく、VirtualBox等のホスト型仮想環境の構築・設定を支援する自動化ツール
- Dockerはコンテナ型仮想化技術
    - コンテナ（LXC, Linux Containers）はLinuxカーネルを用いた技術であるため、Windows上で構築するには、ホスト型仮想環境上にLinuxディストリビューションを起動し、その中でDockerを構築するしかない
    - Windows Pro以上のエディションであれば、Hyper-Vを利用してWindowsネイティブなDockerを使える（ハイパーバイザ型コンテナ）

***

## Docker
Docker
: OS・ミドルウェア・ファイルシステム全体を**イメージ**という単位で取り扱い、まるごとやりとり出来るツール

### 特徴
- 仮想環境は**コンテナ型**と呼ばれるもので、ホストOSを直接アクセスするためオーバーヘッドが少ない
- 環境構築が容易（Dockerfileに環境設定を記述するだけで、必要な環境を自動で構築してくれる）
- コンテナは移植性(ポータビリティ)が高く、Dockerさえインストールされていれば、全く同じ環境でアプリを動かせる
- ホストOSからはコンテナは１プロセスとして認識される


### Dockerが解決するもの
Dockerはアプリケーションとその実行環境を統合的に管理する為のソリューションであるため、開発環境におけるOSレベルのライブラリ、ミドルウェアのバージョン、環境設定は、常に本番環境と同じものにすることが可能

すなわち、Dockerにより本番環境へのデプロイ時の最大の不安要素が解消される


### Dockerの原則
1. 1コンテナにつき1プロセス
    - 1つのコンテナ内に複数プロセス(例: Rails, Nginx, MySQL)を詰め込むと、コンテナの再起動などが気軽にできない
2. コンテナ内で完結させる
    - 使用するミドルウェアやツールなどはすべてホスト側ではなくコンテナ上で管理すること　
    - これにより、バージョンアップやメンテはDockerfile上で管理できる


### Docker専用ディストリビューション
あらゆるプログラムをDocker上で動かすことを前提とした、Docker専用ディストリビューションが存在する

- CoreOS
    - systemd, etcd, fleet, Docker等の基本ツールのみをそろえた軽量Linux
    - CoreOS社のコンテナエンジンrktも使える
    - 基本がLinuxなため難易度は高めだが、柔軟性も高い
- RancherOS
    - カーネル上でシステムDockerが動作し、Docker以外のものが一切ない
    - CoreOS以上に軽量＆起動速度速い
    - Dockerの使い方さえわかれば学習コストは高くない

最近のWindows開発環境のトレンドとしては、VirtualBox + Vagarnt + CoreOS + Dockerが熱い？

なお、Windows10 Pro以上なら **Docker Desktop** を利用するのが良い

特に筆者は Vagrant の意味不明な挙動が嫌になったため、Docker Desktop を利用している

---------------------------------------------------------------------------------------

# Docker環境構築

ここでは、環境に応じてDockerのセットアップを行う

1. Ubuntu 18.04 LTS:
    - [1.1.Dockerインストール_Ubuntu.md](./1.1.Dockerインストール_Ubuntu.md) を参照
2. Windows10 Pro 以上:
    - [1.2.DockerDesktopインストール.md](./1.2.DockerDesktopインストール.md) を参照
3. Windows10 Pro 未満:
    - [1.3.Vagrant+CoreOS+Docker環境構築.md](./1.3.Vagrant+CoreOS+Docker環境構築.md) を参照

なお、**DockerCompose**というソフトウェアもインストールしているが、これは複数のDockerコンテナをまとめてビルドするのに便利なツールである

-----------------------------------------------------------------------------------------

# Docker入門


## Dockerチュートリアル

まずは、Docker上に`webserver`コンテナを作り、その中に`nginx`イメージをインストール＆実行してみる

```bash
# ローカルに保存されているイメージを確認
$ docker images
REPOSITORY          TAG                 IMAGE ID            CREATED             VIRTUAL SIZE

# -> 一つもイメージを保存していないため、現在は何もリストされていない

# nginxイメージを取得する
$ docker pull nginx
Using default tag: latest
latest: Pulling from library/nginx
 : (略)

# -> docker images コマンドで nginxイメージが保存されていることを確認

# `webserver`コンテナに`nginx`イメージから起動し、ローカルポート8080番をコンテナの80番ポートに繋げる
$ docker run -d -p 8080:80 --name webserver nginx
```

ホストマシンのブラウザで、 http://localhost:8080 にアクセスし、nginxサーバーが稼働していることを確認出来たら成功

※ Vagrant環境なら http://Vagrant内仮想マシンのプライベートIP:8080

***

## Docker基本操作

### ローカルに保存されているイメージの一覧表示
```bash
$ docker images
```

### イメージをローカルから削除
```bash
$ docker rmi (イメージ名)
```

### コンテナ一覧の確認
```bash
$ docker ps

CONTAINER ID        IMAGE               COMMAND                  CREATED             STATUS              PORTS                  NAMES
e56ade03b68b        nginx               "nginx -g 'daemon of…"   37 minutes ago      Up 36 minutes       0.0.0.0:8080->80/tcp   webserver
```

### コンテナ内コマンドの実行
```bash
$ docker exec -it <コンテナ名> <コマンド>

# コンテナ内に入ってコマンドを実行するには bashコマンドを実行すればよい
$ docker exec -it <コンテナ名> bash
```

### コンテナの終了
```bash
$ docker kill (CONTAINER ID もしくは NAMES)
$ docker stop -f (待ち秒数) (CONTAINER ID もしくは NAMES)

# 例: 上記`webserver`コンテナを終了する場合
$ docker kill e56ade03b68b

# 例: `webserver`コンテナを3秒後に終了する場合
$ docker stop -f 3 webserver
```

### コンテナの起動
```bash
$ docker start (CONTAINER ID もしくは NAMES)
```

### コンテナの再起動
```bash
$ docker restart (CONTAINER ID もしくは NAMES)
```

### コンテナの削除
```bash
$ docker rm (CONTAINER ID もしくは NAMES)

# ※コンテナを削除する場合は、先にそのコンテナを終了しておく必要がある
```

### 不要なコンテナ・イメージを一括削除
削除は、`コンテナ => イメージ` の順で行う
```bash
# 以下のコマンドは全て Docker 1.13 以降のバージョンで使用可能
docker container prune  # 停止しているコンテナを全て削除
docker volume prune  # 使われていないボリュームを全て削除
docker image prune  # コンテナが使っていないイメージを全て削除
```

### コンテナ・イメージの全削除
```bash
docker rm -f `docker ps -a -q`  # 全コンテナ削除
docker volume rm `docker volume ls -q`  # 全ボリューム削除
docker rmi -f `docker images -q`  # 全イメージ削除
```
