# Windows Setup

## Environment

- OS: Windows 10
- PowerShell: `v2+`
    -  minimum is v3 for install from this website due to TLS 1.2 requirement
- .NET Framework: `4+`

***

## TODO List

基本的に以下のコマンドはすべて PowerShell (管理者権限) 上で実行するものとする

- [ ] Install Chocolatey package manager
    - `> Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://community.chocolatey.org/install.ps1'))`
- [ ] Setup C++ Compiler
    - Install (ネットワーク環境にもよるが1時間程度かかるので、その間に先の Setup を進める)
        - `> choco install -y visualstudio2019buildtools --package-parameters "--add Microsoft.VisualStudio.Workload.VCTools --includeRecommended --includeOptional --passive"`
    - PC再起動
        - `> shutdown /r /t 0`
- [ ] Setup VSCode editor
    - Install: `> choco install -y vscode`
    - Settings: Read [Setup VSCode](../README.md#setup-vscode)
- [ ] Setup Git for Windows
    - Install: `> choco install -y git`
    - Settings:
        - `> git config --global user.name 'your-name'`
        - `> git config --global user.email 'your-mail@example.com'`
        - `> git config --global core.editor 'code -w'`
        - `> git config --global core.autocrlf false`
        - `> git config --global core.quotepath false`
- [ ] Setup Node.js
    - Read [Node.js setup on Windows 10](../03-frontend/01-modern-frontend/README.md#setup-on-windows-10)
- [ ] Setup JupyterLab (Julia + Python)
    - Read [Julia 1.6.1 on Windows 10](../05-algorithm/README.md#julia-161-on-windows-10)
- [ ] Setup Rust (**C++ Compiler の Setup が完了してから行うこと**)
    - Read [Rust setup on Windows 11](https://github.com/amenoyoya/rust-tuto/blob/main/SetupWindows.md)
- [ ] Setup WSL2
    - Read [WSL2開発環境構築](../WSL2開発環境構築.md)
    - ※ ストアアプリ版が正式リリースされた場合は、そちらを使うほうが良い

### Utility softwares
- Google Chrome ブラウザ
    - `> choco install -y googlechrome`
- Google日本語入力 IME
    - `> choco install -y googlejapaneseinput`
- Thunderbird メールクライント
    - `> choco install -y thunderbird`
- VMWare Workstation Player
    - `> choco install -y vmware-workstation-player`
- チャット・ミーティング系ソフトウェア
    - Discord: `> choco install -y discord`
    - Teams: `> choco install -y microsoft-teams`
    - Slack: `> choco install -y slack`
    - Zoom: `> choco install -y zoom`
- Office・メディア系ソフトウェア
    - LibreOffice: `> choco install -y libreoffice-fresh`
    - PDF XChange Viewer: `> choco install -y pdfxchangeviewer --version=2.5.308.0`
    - MPC-BE: `> choco install -y mpc-be`
- その他ユーティリティ
    - Rainmeter: `> choco install -y rainmeter`
    - posh-git: 以下参照

#### posh-git
posh-git は PowerShell 上に git ステータスを表示できるようにするツール（要 Git for Windows）

管理者権限 PowerShell で以下の手順に従ってインストール・設定する

https://qiita.com/kaitaku/items/bd927f8139947847cee1

```powershell
# PowerShell version 確認 (Version 5.x 以上が必要)
> $PSVersionTable.PSVersion

# NuGet インストール (未インストールであれば)
> Install-Module PowerShellGet -Scope CurrentUser -Force -AllowClobber

# PowerShellGet インストール (未インストールであれば)
> Install-Module -Name PowerShellGet -Force

# PowerShellGet バージョン更新 (-AllowPrerelease オプションの使えるバージョンが必要)
> Update-Module -Name PowerShellGet

# -- 一度 PowerShell を開き直す

# posh-git インストール
> PowerShellGet\Install-Module posh-git -Scope CurrentUser -AllowPrerelease -Force
> Import-Module posh-git

# 管理者以外のユーザでも使えるように設定変更
> Add-PoshGitToProfile
> Add-PoshGitToProfile -AllHosts -Force
> Add-PoshGitToProfile -AllUsers -AllHosts -Force

# posh-git の設定項目は以下のコマンドで確認できる
> $GitPromptSettings

## 例: 現在のパスをオレンジ色にしたい場合
## > $GitPromptSettings.DefaultPromptPath.ForegroundColor = 'Orange'
```
