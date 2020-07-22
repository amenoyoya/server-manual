# GitHub の利用方法

## GitHub

- GitHub はソフトウェア開発のプラットフォームである
- GitHub には8000万件以上ものプロジェクトがホスティングされており、2700万人以上のユーザーがプロジェクトを探したり、フォークしたり、コントリビュートしたりしている
- 2018年6月に Microsoft により買収され、プライベートリポジトリの利用が無料・無制限となった

### Setup
[GitHub](https://github.com) から登録

登録メールアドレス宛てに確認メールが届くため、メール内の **Verify mail address** リンクをクリックして、アカウントを有効化する

### GitHubへのSSHキーの登録
ローカルリポジトリと GitHub リモートリポジトリとのやり取りをするにあたり、普通に GitHub のアカウント名とパスワードを使って https通信を行っても良いが、セキュリティ的にはあまりよろしくない（ついでに言うと、毎回パスワードを入力するのが面倒くさい）

そのため、GitHub とやり取りを行うための SSH キーを登録しておく

```bash
# SSH設定ディレクトリ作成（Ubuntu 20.04 on WSL2 では多分最初から作られているはず）
$ mkdir -p ~/.ssh

# SSHキー作成（名前はわかりやすいものにすれば良い）
$ ssh-keygen -t rsa -f ~/.ssh/github_id_rsa
# => /home/<ユーザー名>/.ssh/ に github_id_rsa（秘密鍵）とgithub_id_rsa.pub（公開鍵）生成

# 秘密鍵のパーミッションは 600 (rw-------) にしておく
$ chmod 600 ~/.ssh/github_id_rsa

# ~/.ssh/config にGitHubへの接続設定を追加
## Host 設定名は自分で使いやすい名前で良い（複数指定可能; 以下の場合 github, github.com を設定）
## 秘密鍵（IdentityFile）の名前は自分でつけたものに適宜変更
$ tee -a ~/.ssh/config << EOS
Host github github.com
    HostName github.com
    User git
    IdentityFile ~/.ssh/github_id_rsa
EOS
```

- GitHub > 右上ロゴ > Settings
    - サイドバー > SSH and GPG keys > New SSH Key
        - Title: `GitHub development key` (何でも良い)
        - Key: 作成した `github_id_rsa.pub` (公開鍵) の内容を貼り付け
- GitHub に SSH 接続できるか確認
    ```bash
    # ~/.ssh/config で設定した Host 設定名で SSH 接続
    ## 今回の場合 github もしくは github.com で SSH 接続可能
    $ ssh -T github

    # => 初回は続行するか聞かれるため `yes` と打つ
    ## Hi アカウント名! You've successfully authenticated, but GitHub does not provide shell access. と表示されたら成功
    ```

***

## GitHub リポジトリ作成

- [GitHubダッシュボード](https://github.com/dashboard) > Repositories > New
    - Repository name: リポジトリ名を指定
    - Description: リポジトリの説明文を入力（任意）
    - Public/Private: 公開するか非公開にするか選択
        - Microsoft に買収されて以降、Private リポジトリの利用が無制限になったため、オープンソースにするつもりがなければ Private を選んでおいて間違いない
    - => **Create repository** で新規リポジトリを作成する
- 作成したリポジトリは `https://github.com/<アカウント名>?tab=repositories` で一覧確認できる
- ローカルリポジトリに、作成した GitHub リモートリポジトリを登録する

```bash
# プロジェクト作成
$ mkdir project
$ cd project

# Git 管理開始
$ git init

# GitHubアカウント登録時に登録したメールアドレスとユーザー名を設定する
## --global オプションを付けると、グローバル設定を変更してしまうため注意
$ git config user.email '<メールアドレス>'
$ git config user.name '<ユーザー名>'

# GitHub 側で作っておいたリポジトリのURL（リモートリポジトリ）を登録
## これにより、ローカルでの作業記録を GitHub リポジトリに反映できるようになり、複数人で GitHub を通して作業できるようになる
### リポジトリ名は基本的に origin にする
### リポジトリURLは SSH 通信を行うため git@github.com:<アカウント名>/<GitHubリポジトリ名>.git という形式で登録する
$ git remote add origin 'git@github.com:<アカウント名>/<GitHubリポジトリ名>.git'

# 適当に開発

# 作業記録をコミット
$ git add --all
$ git commit -m '適当に開発'

# ローカルでの作業記録を GitHub リモートリポジトリに push（アップロード）
## -u <認識名> オプションを使用することで、以降、常に指定リモートリポジトリに push できるようになる
$ git push -u origin master

# => 以降は git push だけで、origin (GitHub リモートリポジトリ) に master ブランチを push できる
```

***

## Git LFS について

GitHub に push できるファイルサイズは 100MB に制限されている

これを回避するためには、[Git LFS](https://github.com/git-lfs/git-lfs/wiki/Installation)（Git Large File System）という拡張機能を導入する必要がある

### Git LFS 導入
```bash
# Ubuntu の場合、以下のコマンドで git-lfs をインストール
$ sudo apt install -y git-lfs

# リポジトリ内で git-lfs を使えるようにする
$ git lfs install

# LARGE_FILE を git-lfs でトラッキング
$ git lfs track LARGE_FILE
# => .gitattributes ファイルに設定が書き込まれる

# 以降は通常通り、ステージング＆反映すればOK
$ git add --all
$ git commit -m 'lfs track: LARGE_FILE'
```

### GitHub LFS の料金
なお、GitHub の LFS は無料では 1GB しか使えないため、それ以上使う場合は 5.00USD/50GB/月 の課金が必要になる

コスパは正直あまり良くないため、AWS S3 などの安価なクラウドストレージと連携したり、NAS をリモートリポジトリ化するなど、いろいろと工夫する必要がある
