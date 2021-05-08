FROM centos:6

COPY ./authorized_keys /root/.ssh/authorized_keys

RUN : 'fix yum repository' && \
    sed -i -e "s|mirror\.centos\.org/centos/\$releasever|ftp\.iij\.ad\.jp/pub/linux/centos-vault/6\.10|g" /etc/yum.repos.d/CentOS-Base.repo && \
    sed -i -e "s|#baseurl=|baseurl=|g" /etc/yum.repos.d/CentOS-Base.repo && \
    sed -i -e "s|mirrorlist=|#mirrorlist=|g" /etc/yum.repos.d/CentOS-Base.repo && \
    : 'install packages' && \
    yum update -y && yum install -y \
        glibc-locale-source glibc-langpack-ja \
        sudo vim openssh-server openssh-clients procps libxslt tar unzip && \
    : 'set locale' && \
    localedef -f UTF-8 -i ja_JP ja_JP.UTF-8 && \
    : 'install cron' && \
    yum install -y cronie && \
    chkconfig crond on && \
    : 'change root password' && \
    echo 'root:root' | chpasswd && \
    : 'ssh setting' && \
    chmod 600 ~/.ssh/authorized_keys && \
    mkdir -p /var/run/sshd && \
    echo "\n\n" | ssh-keygen -t rsa -f /etc/ssh/ssh_host_rsa_key && \
    echo "\n\n" | ssh-keygen -t ecdsa -f /etc/ssh/ssh_host_ecdsa_key && \
    sed -iE 's/^#\?\s*PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config && \
    sed -iE 's/^#\?\s*PermitRootLogin.*/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    chkconfig sshd on && \
    : 'install apache, php73' && \
    yum install -y epel-release && \
    yum -y update --enablerepo=epel,remi,remi-php73 && \
    yum install -y \
        libwebp libmcrypt libtool-ltdl libtidy libXpm libtiff \
        ncurses-compat-libs gd-last autoconf automake httpd && \
    yum install -y --enablerepo=remi,remi-php73 t1lib && \
    yum install -y --enablerepo=epel,remi,remi-php73 php php-xml php-mbstring php-gd && \
    chkconfig httpd on && \
    : 'install mysql' && \
    yum localinstall --nogpgcheck -y https://dev.mysql.com/get/mysql57-community-release-el6-8.noarch.rpm && \
    yum -y update --enablerepo=mysql57-community && \
    yum install -y --enablerepo=mysql57-community mysql-community-server mysql-community-devel && \
    chkconfig mysqld on && \
    : 'install aws cli' && \
    curl https://awscli.amazonaws.com/awscli-exe-linux-x86_64.zip -o awscliv2.zip && \
    unzip ./awscliv2.zip && \
    ./aws/install && \
    rm -rf ./aws ./awscliv2.zip && \
    : 'clear yum cache' && \
    yum clean all

ENV TZ="Asia/Tokyo" \
    LANG="ja_JP.UTF-8" \
    LANGUAGE="ja_JP:ja" \
    LC_ALL="ja_JP.UTF-8"

EXPOSE 22 80

# systemctl関連コマンドを使えるようにするために /sbin/init の実行が必要
## execコマンドはシェルを置き換えてしまうため、以下のように記述しても後ろのコマンド（sshd, httpd 開始コマンド）は実行されない
## => ビルド時（RUNステートメント）に各種サービスの自動起動をONにしておくことで代替するしかない
# CMD ["bash", "-c", "exec /sbin/init && /usr/sbin/sshd -D && /usr/sbin/httpd -k start"]
CMD ["/sbin/init"]
