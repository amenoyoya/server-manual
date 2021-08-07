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
RUN julia -e 'using Pkg;Pkg.add("IJulia");' && \
    julia -e 'using Pkg;Pkg.add("HTTP");' && \
    julia -e 'using Pkg;Pkg.add("DataFrames");' && \
    julia -e 'using Pkg;Pkg.add("Match");' && \
    julia -e 'using Pkg;Pkg.add("PyCall");' && \
    julia -e 'using Pkg;Pkg.add("PyPlot");' && \
    julia -e 'using Pkg;Pkg.add("Images");' && \
    julia -e 'using Pkg;Pkg.add("ImageMagick");' && \
    julia -e 'using Pkg;Pkg.add("Nettle");'

# スタートアップコマンド（docker up の度に実行される）: activate crontab && launch supervisor daemon
## 環境変数を引き継いで sudo 実行するため -E オプションをつけている
CMD ["sudo", "-E", "/bin/bash", "-c", "busybox crontab /var/spool/cron/crontabs/user && cd /root/ && /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf"]
