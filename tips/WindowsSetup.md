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
- チャット・ミーティング系ソフトウェア
    - Discord: `> choco install -y discord`
    - Teams: `> choco install -y microsoft-teams`
    - Slack: `> choco install -y slack`
    - Zoom: `> choco install -y zoom`
- Office・メディア系ソフトウェア
    - LibreOffice: `> choco install -y libreoffice-fresh`
    - PDF XChange Viewer: `> choco install -y pdfxchangeviewer --version=2.5.308.0`
    - MPC-BE: `> choco install -y mpc-be`
