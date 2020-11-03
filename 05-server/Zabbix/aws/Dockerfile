FROM amazonlinux:2

COPY ./id_rsa.pub /root/.ssh/authorized_keys

RUN : 'install packages' && \
    yum update -y && yum install -y \
        glibc-locale-source glibc-langpack-ja \
        sudo vim openssh-server openssh-clients && \
    : 'set locale' && \
    localedef -f UTF-8 -i ja_JP ja_JP.UTF-8 && \
    : 'change root password' && \
    echo 'root:root' | chpasswd && \
    : 'ssh setting' && \
    chmod 600 ~/.ssh/authorized_keys && \
    mkdir -p /var/run/sshd && \
    echo "\n\n" | ssh-keygen -t rsa -f /etc/ssh/ssh_host_rsa_key && \
    echo "\n\n" | ssh-keygen -t ecdsa -f /etc/ssh/ssh_host_ecdsa_key && \
    echo "\n\n" | ssh-keygen -t ed25519 -f /etc/ssh/ssh_host_ed25519_key && \
    sed -iE 's/^#\?\s*PubkeyAuthentication.*/PubkeyAuthentication yes/' /etc/ssh/sshd_config && \
    sed -iE 's/^#\?\s*PermitRootLogin.*/PermitRootLogin yes/' /etc/ssh/sshd_config && \
    : 'install apache, php73' && \
    yum install -y https://dl.fedoraproject.org/pub/epel/epel-release-latest-7.noarch.rpm && \
    rpm -Uvh http://rpms.famillecollet.com/enterprise/remi-release-7.rpm && \
    yum -y update --enablerepo=epel,remi,remi-php73 && \
    yum install -y \
        libwebp libmcrypt libtool-ltdl libtidy libXpm libtiff \
        ncurses-compat-libs gd-last autoconf automake httpd && \
    yum install -y --enablerepo=remi,remi-php73 t1lib && \
    yum install -y --disablerepo=amzn2-core --enablerepo=epel,remi,remi-php73 php php-mbstring php-gd && \
    : 'clear yum cache' && \
    yum clean all

ENV TZ="Asia/Tokyo" \
    LANG="ja_JP.UTF-8" \
    LANGUAGE="ja_JP:ja" \
    LC_ALL="ja_JP.UTF-8"

EXPOSE 22 80
CMD ["bash", "-c", "exec /sbin/init && systemctl start sshd"]
