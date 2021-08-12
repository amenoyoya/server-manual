# Git Tips

Git の少し特殊な使い方やトラブルシューティング集

## GitHub の使い方

リモートリポジトリとして GitHub を利用したい場合の作業

[GitHub.md](./GitHub.md) を参照

***

## エラー解決: The following untracked working tree files would be overwritten by merge

サーバへのデプロイを Git で行う場合、サーバ内でファイルの変更や新規作成を行ったりしていると、`git pull` 時に `The following untracked working tree files would be overwritten by merge` というエラーが発生することがある

このエラーが出た場合、サーバで作成・更新した方の変更を優先したい場合は、Git の設計を間違えている

この場合、対象のファイルは `.gitignore` で Git の管理対象から外すべきである

一方、Git 側で更新された変更を優先したい場合、デプロイコマンドは以下のようにすると良い

```bash
# リモートリポジトリ: origin の master ブランチを pull
$ git pull origin master

# 上記のようなエラーが発生した場合、シェルコマンドの終了ステータスは 1 になる（成功時は 0）
$ if [ "$?" = "1" ]; then
    # git pull が失敗した場合は FETCH_HEAD の状態にハードリセットすれば良い
    git fetch origin
    git reset --hard FETCH_HEAD
fi
```

***

## リモートのタグやブランチ削除

```bash
# ローカルでタグ・ブランチを削除
$ git tag -d <tag_name>
$ git branch -d <branch_name>

# リモートに空のタグ・ブランチをpush
$ git push origin :<tag_or_branch_name>

## もしくは --deleteオプションをつけてpush
$ git push --delete origin <tag_or_branch_name>
```

***

## サブモジュール（Git内Git）

Git管理下のサブディレクトリに別のGitがある場合、サブGitはメインGitの管理に入らない

サブGitも含めて管理するには、以下のように**サブモジュール**として登録する必要がある

```bash
$ git submodule add <サブモジュールURL> <サブモジュールディレクトリ>
```

サブモジュールとして登録されたリポジトリは `.gitmodules` ファイルに記述されている

### 手動で追加する場合
たまによく分からないエラーで `git submodule` コマンドが効かないことがある

そのような場合は、以下の2つのファイルを手動で編集する

- `.git/config`
- `.gitmodules`

```conf
[submodule "path/to/submodule"]
    path = path/to/submodule
    url = git@github.com:user/repo.git
```

***

## NASをリモートリポジトリ化

リモートリポジトリとして GitHub などのサービスを使っていると、あまり大きなサイズのファイルはアップロードできない（GitHub 有料オプションの Git LFS を使う場合を除く）

そういった場合、自宅のNASをリモートリポジトリとして使うことがある

```bash
# -- @NAS (192.168.10.100)

# NAS上にリポジトリ用のディレクトリを作成
## 拡張子に .git などをつけると分かりやすい
$ mkdir repo.git
$ cd repo.git

# Gitリポジトリとして初期化
## --bare: 作業ディレクトリを持たない（更新情報のみを持つ）リポジトリを作る
## --shared: 共有リポジトリとしてパーミッションを付与
$ git init --bare --shared
```

```bash
# -- @localhost (Ubuntu 20.04 on WSL2)

# ローカル作業用リポジトリ作成
$ mkdir work
$ cd work
$ git init

# NAS上のリポジトリをリモートリポジトリとして登録
# NAS上に作成したディレクトリが //192.168.10.100/disk1/git/repo.git の場合、以下のようになる
$ git remote add origin //192.168.10.100/disk1/git/repo.git

# 適当に開発を行う

# ローカルコミット
$ git add --all
$ git commit -m '適当に実装'

# origin として登録したリモートリポジトリに push
$ git push -u origin master
# => NAS上のリポジトリ（repo.git）に内容が反映される
```

***

## ローカル開発時のみファイル変更を無視する

### git update-index
- ローカルリポジトリに対してのみ反映される設定用のコマンド
- すでにgitリポジトリ管理下にあり、本番サーバーで動くように調整されているファイルを、ローカル開発用に一時的に変更したい場合などは `--assume-unchanged` で変更を無視させるのが有効

```bash
# ローカル環境での変更を無視する
$ git update-index --assume-unchanged <ファイル名>
```

***

## 過去のコミットの内容を修正

### git commit --amend
- 直前のコミットメッセージを修正したい場合などに使う
- `--date` オプションと併用すれば、コミット時の AuthorData を変更することも可能
    - 過去に遡ってGitHubの草を生やす場合に使えるが、それ以外の用途は思いつかない

```bash
# 直前のコミットメッセージを修正
$ git commit --amend -m '修正したコミットメッセージ'

# 直前のコミット日時を 2019/6/28(Fri)22:30 に偽装
$ git commit --amend -m 'コミット日時を偽装' --date='Fri Jun 28 22:30:00 2019 +0900'

# コミット履歴を確認
## --pretty=fuller オプションでコミット日時を確認できる
$ git log --pretty=fuller
commit ...
Author:     gituser <user@gexample.com>
AuthorDate: Fri Jun 28 22:30:00 2019 +0900 # <= AuthorDateが修正される
Commit:     gituser <user@gexample.com>
CommitDate: Sat Jun 29 17:19:15 2019 +0900 # <= CommitDateは修正されないが、GitHubの草には関係ない
```

### 過去のCommitterを変更する
```bash
# 過去のCommitterを一括変更する
$ name='author_name'
$ email='committer@example.com'
$ git filter-branch -f --env-filter "GIT_AUTHOR_NAME='$name'; GIT_AUTHOR_EMAIL='$email'; GIT_COMMITTER_NAME='$name'; GIT_COMMITTER_EMAIL='$email';" HEAD

# 既にpush済みのブランチに変更を加えた場合、force pushする必要がある
$ git push -f
```

特定のコミットの Author および Committer を変更したい場合は if シェルを使うと良い

```bash
$ git filter-branch --env-filter '
    if [ $GIT_COMMIT = "<commit_id>" ]
    then
        export GIT_AUTHOR_NAME="<author_name>"
        export GIT_AUTHOR_EMAIL="<author_email>"
        export GIT_COMMITTER_NAME="<committer_name>"
        export GIT_COMMITTER_EMAIL="<committer_email>"
    fi'
```

### 過去のコミットからファイルを削除・更新する
公開リポジトリにパスワード情報ファイルなどをコミットしてしまった場合などに使う

```bash
# コミット履歴からファイル削除
$ target_file='password.txt'
$ git filter-branch -f --index-filter "git rm --cached --ignore-unmatch $target_file" HEAD

## ↑ターゲットファイルがディレクトリの場合は "git rm -r --cached --ignore-unmatch $target_file" にする

## 過去コミット内の特定のファイルの内容を変更する場合:
$ sed_command='sed -i /^PASSWORD=/d password.txt'
$ git filter-branch --tree-filter "$sed_command" HEAD

# 既にpush済みなら force push
$ git push -f
```

***

## Gitのブランチ名を変更

ローカルのGitブランチ名を変更する

```bash
$ git branch -m <古いブランチ名> <新しいブランチ名>

# 今開いているブランチ名を変更する場合
$ git branch -m <新しいブランチ名>
```
