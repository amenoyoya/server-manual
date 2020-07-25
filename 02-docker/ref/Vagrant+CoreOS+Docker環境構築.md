# Vagrant + CoreOS + Docker 環境の構築

VirtualBox + Vagrant で CoreOS (Docker専用Linuxディストリビューション) をインストールする

※ WindowsパッケージマネージャとしてChocolateyインストール済みの環境を想定

## Vagrantのインストール

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# VirtualBox, Vagrant 導入
> choco install -y virtualbox vagrant

# 一度コンピュータを再起動する
> Restart-Computer
```

### Vagarntプラグインのインストール
```powershell
# 管理者権限PowerShell

# Vagrant仮想環境にバーチャルホスト名を設定するためのプラグイン
# (C:\Windows\System32\drivers\etc\hosts を自動で書き換えるプラグイン)
> vagrant plugin install vagrant-hostsupdater

# WindowsファイルシステムとLinuxファイルシステムの相互変換を行うプラグイン
> vagrant plugin install vagrant-vbguest

# WindowsのNTFSマウントで、LinuxのNFSマウントを可能にするプラグイン
> vagrant plugin install vagrant-winnfsd
> vagrant plugin install vagrant-ignition
```

***

## Windows上でシンボリックリンクを張れるように設定

Windowsのシンボリックリンク設定がされていないと、Linux on VirtualBox(Vagrant) 上で npm インストールする時などにエラーが起こる

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# シンボリックリンクを有効化
> fsutil behavior set SymlinkEvaluation L2L:1 R2R:1 L2R:1 R2L:1

# 確認
> fsutil behavior query symlinkevaluation
ローカルからローカルへのシンボリック リンクは有効です。
ローカルからリモートへのシンボリック リンクは有効です。
リモートからローカルへのシンボリック リンクは有効です。
リモートからリモートへのシンボリック リンクは有効です。
```

***

## CoreOSインストール

以下の設定を全て完了したVagrantファイルは [coreos-vagrant](./coreos-vagrant) フォルダにある

### Vagrantファイルの取得
`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# git for windows 導入
> choco install -y git

# coreosフォルダを作って、その中に CoreOS の git をクローンする
> git clone https://github.com/coreos/coreos-vagrant/ coreos

# coreosフォルダに移動
> cd coreos
```

### VirtualBox Host-Only Network 確認
```powershell
# ネットワーク構成確認
> ipconfig

# => VirtualBox Host-Only Network の IPv4 アドレス確認
# => 192.168.XX.X をメモしておく
```

### Vagrantfileの編集
- `Vagrantfile` を編集
    ```ruby
     : (略)
    $vm_memory = 2048  # メモリは多めに取る
    $shared_folders = {'./share' => '/home/core/share'}  # ホスト-ゲスト間で共有するフォルダを指定
     :
    config.vm.provider :virtualbox do |vb|
      # ホスト名設定（要 管理者権限 & vagrant-hostsupdaterプラグイン）
      config.hostsupdater.aliases = ['example.com']
       :
      # VirtualBoxから共有フォルダでシンボリックリンクを張れるよう設定
      vb.customize ["setextradata", :id, "VBoxInternal2/SharedFoldersEnableSymlinksCreate/.","1"]
      config.ignition.config_obj = vb
    end

    # CoreOSのPrivateIPを設定
    ## VirtualBox Host-Only Network のIPv4アドレス（192.168.XX.X）の上位3組を指定
    ip = "192.168.56.#{i+100}"
    config.vm.network :private_network, ip: ip
     : (略)
    ```

### CoreOSの起動
```powershell
# vagrant://coreos:/home/core/share/ と共有する share フォルダを作っておく
> mkdir share

# Vagrant 起動
> vagrant up

# 状態確認
> vagrant status

# => 状態がrunningになっていれば起動完了
```

### SSH接続
適当なSSHクライアントを用いて、以下の設定でSSH接続する

- IPアドレス: CoreOS起動時に表示される`SSH address`（デフォルトだと`127.0.0.1`）
- TCPポート: CoreOS起動時に表示される`SSH address`のポート（デフォルトだと`2222`）
- ユーザー: `core`（※`vagrant`ではない）
- SSH公開鍵: `~\.vagrant.d\insecure_private_key`を指定

もしくは PowerShell からSSH接続することも可能

```powershell
# Vagrantfile のあるディレクトリで以下のコマンド実行
> vagrant ssh
```

### CoreOSの手動アップデート
```bash
# -- powershell@localhost

# CoreOSにSSH接続
> vagrant ssh

# -- core@vagrant-coreos

# CoreOSアップデート
$ sudo update_engine_client -update
$ exit

# -- powershell@localhost

# Windows側に戻り、Vagrant再起動
> vagrant reload
```

***

## DockerComposeインストール

```bash
# -- powershell@localhost

# CoreOSにSSH接続
> vagrant ssh

# -- core@vagrant-coreos

# CoreOSはデフォルトでDockerが入っているはず
## バージョン確認を行う
$ docker --version
Docker version 18.06.3-ce, build d7080c1

# CoreOSの /usr/bin/ は Read-Only なため /opt/bin/ を作成する
$ sudo mkdir -p /opt/bin

# 念のため $PATH を確認しておく
$ echo $PATH
/usr/local/sbin:/usr/local/bin:/usr/sbin:/usr/bin:/opt/bin

# ※ /opt/bin が PATH に含まれていない場合
# $ echo 'export PATH="$PATH:/opt/bin"' >> ~/.bashrc
# $ source ~/.bashrc

# githubからdocker-composeの最新版をダウンロード
# 最新バージョンは https://github.com/docker/compose/releases/latest/ で確認できる
$ sudo curl -L https://github.com/docker/compose/releases/download/1.24.0/docker-compose-`uname -s`-`uname -m` -o /opt/bin/docker-compose

# docker-composeコマンドの実行を許可
$ sudo chmod +x /opt/bin/docker-compose

# バージョン確認
$ docker-compose -v
docker-compose version 1.24.0, build 0aa59064
```

***

## Vagrantのネットワーク処理が異常に遅い場合の解決策

- 参考:
    - https://qiita.com/s-kiriki/items/357dc585ee562789ac7b
    - https://github.com/hashicorp/vagrant/issues/1172

Vagrant内のネットワーク処理（外部APIを叩く等）が極端に遅い場合、以下の設定を追加すると上手くいくことがある

### Vagrantfile
```ruby
config.vm.provider :virtualbox do |vb|
  # 以下の設定を追記
  vb.customize ["modifyvm", :id, "--natdnsproxy1", "off"]
  vb.customize ["modifyvm", :id, "--natdnshostresolver1", "off"]

  # ...(略)...
end
```
