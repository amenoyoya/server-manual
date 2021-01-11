#!/bin/bash

cd $(dirname $0)
export USER_ID="${USER_ID:-$UID}"

case "$1" in
"init")
    mkdir ./.cache/
    mkdir -p ./docker/conf/
    tee ./.cache/ << \EOS
/*
!/.gitignore
EOS
    touch ./docker/conf/crontab
    tee ./docker/conf/supervisord.conf << \EOS
[supervisord]
nodaemon=true

[program:crond]
command=/bin/busybox crond -f
process_name=%(program_name)s
logfile_maxbytes=10MB
logfile_backup=10
stdout_logfile=/var/log/supervisor/%(program_name)s.log
stderr_logfile=/var/log/supervisor/%(program_name)s-error.log

[program:ipynb]
command=/opt/conda/bin/jupyter-lab --allow-root
directory=/work/
user=user
process_name=%(program_name)s
logfile_maxbytes=10MB
logfile_backup=10
stdout_logfile=/var/log/supervisor/%(program_name)s.log
stdout_logfile=/var/log/supervisor/%(program_name)s-error.log
numprocs=1
autostart=true
autorestart=true
redirect_stderr=true
EOS
    tee ./docker/conf/jupyter_notebook_config.py << \EOS
# Copyright (c) Jupyter Development Team.
# Distributed under the terms of the Modified BSD License.

from jupyter_core.paths import jupyter_data_dir
import subprocess
import os
import errno
import stat

c = get_config()  # noqa: F821
c.NotebookApp.ip = '0.0.0.0'
c.NotebookApp.port = 8888
c.NotebookApp.open_browser = False

# https://github.com/jupyter/notebook/issues/3130
c.FileContentsManager.delete_to_trash = False

# token: http://localhost:8888/?token=jupyter0docker
c.NotebookApp.token = 'jupyter0docker'

# Generate a self-signed certificate
if 'GEN_CERT' in os.environ:
    dir_name = jupyter_data_dir()
    pem_file = os.path.join(dir_name, 'notebook.pem')
    try:
        os.makedirs(dir_name)
    except OSError as exc:  # Python >2.5
        if exc.errno == errno.EEXIST and os.path.isdir(dir_name):
            pass
        else:
            raise

    # Generate an openssl.cnf file to set the distinguished name
    cnf_file = os.path.join(os.getenv('CONDA_DIR', '/usr/lib'), 'ssl', 'openssl.cnf')
    if not os.path.isfile(cnf_file):
        with open(cnf_file, 'w') as fh:
            fh.write('''\
[req]
distinguished_name = req_distinguished_name
[req_distinguished_name]
''')

    # Generate a certificate if one doesn't exist on disk
    subprocess.check_call(['openssl', 'req', '-new',
                           '-newkey', 'rsa:2048',
                           '-days', '365',
                           '-nodes', '-x509',
                           '-subj', '/C=XX/ST=XX/L=XX/O=generated/CN=generated',
                           '-keyout', pem_file,
                           '-out', pem_file])
    # Restrict access to the file
    os.chmod(pem_file, stat.S_IRUSR | stat.S_IWUSR)
    c.NotebookApp.certfile = pem_file

# Change default umask for all subprocesses of the notebook server if set in
# the environment
if 'NB_UMASK' in os.environ:
    os.umask(int(os.environ['NB_UMASK'], 8))
EOS
    tee ./docker/Dockerfile << \EOS
FROM pytorch/pytorch:1.6.0-cuda10.1-cudnn7-devel

# Docker実行ユーザIDを環境変数から取得
ARG UID

# パッケージインストール時に対話モードを実行しないように設定
ENV DEBIAN_FRONTEND=noninteractive

RUN : 'install japanese environment' && \
    apt-get update && apt install -y tzdata language-pack-ja && \
    update-locale LANG=ja_JP.UTF-8 && \
    : 'install development modules' && \
    apt-get install -y wget curl git unzip vim && \
    : 'install supervisor, busybox' && \
    apt-get install -y supervisor busybox-static && \
    mkdir -p /var/log/supervisor/ && \
    : 'Add user $UID (if $UID already exists, then change user id)' && \
    if [ "$(getent passwd $UID)" != "" ]; then usermod -u $((UID + 100)) "$(getent passwd $UID | cut -f 1 -d ':')"; fi && \
    useradd -m -s /bin/bash -u $UID user && \
    apt-get install -y sudo && \
    echo 'user ALL=NOPASSWD: ALL' >> '/etc/sudoers' && \
    : 'install python libraries' && \
    conda config --add channels pytorch && \
    conda config --append channels conda-forge && \
    conda update --all --yes --quiet && \
    conda install --yes --quiet \
        ipywidgets jupyterlab matplotlib nodejs opencv pandas scikit-learn seaborn sympy && \
    conda clean --all -f -y && \
    : 'install jupyter extensions' && \
    jupyter nbextension enable --py --sys-prefix widgetsnbextension && \
    jupyter labextension install @jupyter-widgets/jupyterlab-manager && \
    : 'install julia 1.5.3' && \
    wget -O - https://julialang-s3.julialang.org/bin/linux/x64/1.5/julia-1.5.3-linux-x86_64.tar.gz | tar xvzf - -C /usr/src/ && \
    ln -s /usr/src/julia-1.5.3/bin/julia /usr/local/bin/julia && \
    : 'cleanup apt-get caches' && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# 日本語環境に設定
ENV TZ Asia/Tokyo
ENV LANG ja_JP.UTF-8
ENV LANGUAGE ja_JP:ja
ENV LC_ALL ja_JP.UTF-8

# 作業ディレクトリ
## docker://app:/work/ => host://./
WORKDIR /work/

# 作業ユーザ: Docker実行ユーザ
## => コンテナ側のコマンド実行で作成されるファイルパーミッションをDocker実行ユーザ所有に
USER user

# 作業ユーザで Julia パッケージインストール
RUN julia -e 'using Pkg;Pkg.add("IJulia");Pkg.add("HTTP");Pkg.add("DataFrames");Pkg.add("Match");Pkg.add("PyCall");Pkg.add("Images");Pkg.add("ImageMagick");Pkg.add("Nettle");'

# スタートアップコマンド（docker up の度に実行される）: activate crontab && launch supervisor daemon
## 環境変数を引き継いで sudo 実行するため -E オプションをつけている
CMD ["sudo", "-E", "/bin/bash", "-c", "busybox crontab /var/spool/cron/crontabs/user && cd /root/ && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
EOS
    tee ./.gitignore << \EOS
.ipynb_checkpoints/
EOS
    tee ./.env << \EOS
JUPYTER_PORT=8888
EOS
    tee ./docker-compose.yml << \EOS
# ver 2.4 or 3.6 >= required: enable '-w' option for 'docker-compose exec'
# ver 2.3 >= required: use 'runtime' setting (ver 3.x not supported)
version: "2.4"

networks:
  # プロジェクト内仮想ネットワーク
  ## 同一ネットワーク内の各コンテナはサービス名で双方向通信可能
  appnet:
    driver: bridge
    # ネットワークIP範囲を指定する場合
    # ipam:
    #   driver: default
    #   config:
    #     # 仮想ネットワークのネットワーク範囲を指定
    #     ## 172.68.0.0/16 の場合、172.68.0.1 ～ 172.68.255.254 のIPアドレスを割り振れる
    #     ## ただし 172.68.0.1 はゲートウェイに使われる
    #     - subnet: 172.68.0.0/16

services:
  # app service container: python 3.7 / jupyterlab + pytorch
  app:
    build:
      context: ./docker/
      args:
        # use current working user id
        UID: $USER_ID
    logging:
      driver: json-file
    # restart: always
    # 所属ネットワーク
    networks:
      - appnet
    # ポートフォワーディング
    ports:
      # http://localhost:${JUPYTER_PORT} => service://app:8888
      - "${JUPYTER_PORT:-8888}:8888"
    # DNSサーバにGoogleDNS利用
    dns: 8.8.8.8
    # enable terminal
    tty: true
    volumes:
      # host://./ => service://app:/work/
      - ./:/work/
      # host://./.cache/ => service://app:/home/user/.cache/
      - ./.cache/:/home/user/.cache/
      # Docker socket 共有
      - /var/run/docker.sock:/var/run/docker.sock
      # 設定ファイル
      - ./docker/conf/supervisord.conf:/etc/supervisor/conf.d/supervisord.conf
      - ./docker/conf/crontab:/var/spool/cron/crontabs/user
      - ./docker/conf/jupyter_notebook_config.py:/home/user/.jupyter/jupyter_notebook_config.py
    # nvidia-container-toolkit を runtime として利用
    runtime: nvidia
    environment:
      - NVIDIA_VISIBLE_DEVICES=all
      - NVIDIA_DRIVER_CAPABILITIES=all
EOS
    ;;
"app")
    if [ "$w" != "" ]; then
        docker-compose exec -Tw "$w" app "${@:2:($#-1)}"
    else
        docker-compose exec -T app "${@:2:($#-1)}"
    fi
    ;;
*)
    docker-compose $*
    ;;
esac
