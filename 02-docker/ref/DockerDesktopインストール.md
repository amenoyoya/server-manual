# Docker Desktop (Docker for Windows) インストール

- 必要要件:
    - Windows 10 Pro 以上

Windows用に提供されている Docker コンテナエンジンを利用する

※ Docker Desktop は、Linux版の Docker の動作と互換性のない部分があるため、基本的には推奨していない

※ WindowsパッケージマネージャとしてChocolateyインストール済みの環境を想定

## Hyper/V の有効化

- 前準備として、BIOSで `Virtualization Technology (VTx)` を有効化しておく
    - 大抵はすでに有効化されていると思う
- `Win + X` |> `A` => 管理者権限 PowerShell 起動
    ```powershell
    # Hyper/V 有効化
    > Enable-WindowsOptionalFeature -Online -FeatureName Microsoft-Hyper-V
    この操作を完了するために、今すぐコンピューターを再起動しますか?
    [Y] Yes  [N] No  [?] ヘルプ (既定値は "Y"): # Y を押して再起動する
    ```

### 「構成レジストリキーを読み取れません」エラーが発生する場合
- 端末によっては上記の `Enable-WindowsOptionalFeature` コマンドが失敗することがある
- 基本的には再起動すれば上手くいくが、再起動してもダメな場合は、コントロールパネルから有効化する
    - `Win + R` => `control` と入力してEnter => コントロールパネルが開く
    - コントロールパネル > プログラム > Windowsの機能の有効化または無効化
        - [x] Hyper-V にチェック

***

## Docker Desktop インストール

`Win + X` |> `A` => 管理者権限 PowerShell 起動

```powershell
# docker-desktop 導入
> choco install -y docker-desktop
```

- インストールしたら Windowsスタートメニュー > `Docker Desktop` から起動する
    - **Docker Desktop を使うには会員登録が必要**
    - 設定:
        - 基本的にデフォルトのままでOK
        - Shared Drives:
            - [x] Cドライブにチェック（ログインパスワードの入力が必要）
- 動作確認
    ```powershell
    > docker --version
    Docker version 19.03.1, build 74b1e89

    # Docker Desktop はデフォルトで docker-compose も入っている
    > docker-compose --version
    docker-compose version 1.24.1, build 4667896b
    ```
