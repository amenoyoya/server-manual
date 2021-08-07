# CUDA on WSL2 Tips

## GPU対応 ffmpeg 導入

- 参考:
    - https://qiita.com/yamakenjp/items/7474f210efd82bb28490
    - https://kinakoankon.net/ubuntu-20-04-ffmpeg-4-3-nvenc-hevc-h265-gtx-1050/

```bash
# nvidia-driver 440 (API v9.1) インストール
$ sudo apt install -y libnvidia-encode-440

# nasm, yasm, automake, cmake インストール
$ brew install nasm yasm automake cmake

# /usr/local/src/ のパーミッションを自分所有にする
$ sudo chown -R $USER /usr/local/src/

# libx264 導入
$ cd /usr/local/src/
$ git clone https://code.videolan.org/videolan/x264
$ cd x264
$ ./configure --enable-static --enable-pic
$ make -j$(nproc)
$ sudo make install

# libvpx 導入
$ cd /usr/local/src/
$ git clone https://chromium.googlesource.com/webm/libvpx
$ cd libvpx
$ ./configure --disable-examples --disable-unit-tests --enable-vp9-highbitdepth --as=yasm
$ make -j$(nproc)
$ sudo make install

# libfdk-aac 導入
$ cd /usr/local/src/
$ git clone https://github.com/mstorsjo/fdk-aac
$ cd fdk-aac
$ brew install
$ autoreconf -fiv
$ ./configure --disable-shared
$ make -j$(nproc)
$ sudo make install

# libmp3lame 導入
$ cd /usr/local/src/
$ wget -O - https://downloads.sourceforge.net/project/lame/lame/3.100/lame-3.100.tar.gz | tar -xzvf -
$ cd lame-3.100
$ ./configure --disable-shared --enable-nasm
$ make -j$(nproc)
$ sudo make install

# libopus 導入
$ cd /usr/local/src/
$ git clone  https://github.com/xiph/opus
$ cd opus
$ ./autogen.sh && ./configure --disable-shared
$ make -j$(nproc)
$ sudo make install

# libaom 導入
$ cd /usr/local/src/
$ git clone https://aomedia.googlesource.com/aom
$ mkdir aom_build
$ cd aom_build
$ cmake -G "Unix Makefiles" -DENABLE_SHARED=off -DENABLE_NASM=on ../aom
$ make -j$(nproc)
$ sudo make install

# nv-codec-headers 導入
$ cd /usr/local/src/
$ git clone https://git.videolan.org/git/ffmpeg/nv-codec-headers
$ cd nv-codec-headers
## libnvidia-encode-440 の API バージョンは 9.1 のため、それ用のブランチに checkout
$ git checkout sdk/9.1
$ make -j$(nproc)
$ sudo make install

# ffmpeg 4.3.1 導入
$ cd /usr/local/src/
$ wget -O - http://ffmpeg.org/releases/ffmpeg-4.3.1.tar.gz | tar -xzvf -
$ cd ffmpeg-4.3.1
## nv-codec-headers は /usr/local/lib/ にインストールされるため
## PKG_CONFIG_PATH に /usr/local/lig/pkgconfig を指定してセットアップ
$ PKG_CONFIG_PATH='/usr/local/lib/pkgconfig' ./configure \
    --pkg-config-flags='--static' \
    --extra-cflags='-I/usr/local/include' \
    --extra-ldflags='-L/usr/local/lib' \
    --extra-libs='-lpthread -lm' \
    --enable-openssl \
    --enable-gpl \
    --enable-libaom \
    --enable-libass \
    --enable-libfdk-aac \
    --enable-libfreetype \
    --enable-libmp3lame \
    --enable-libopus \
    --enable-libvorbis \
    --enable-libvpx \
    --enable-libx264 \
    --enable-libx265 \
    --enable-static \
    --enable-cuda \
    --enable-cuvid \
    --enable-nvenc \
    --enable-libnpp \
    --enable-nonfree
$ make -j$(nproc)
$ sudo make install

# ffmpeg のエンコーダとして nvenc が使えるか確認
$ ffmpeg -encoders | grep nvenc

# GPUを使って（hevc_nvenc encoder）適当な mp4 動画を webm に変換
$ ffmpeg -i 'test.mp4' -vcodec hevc_nvenc 'test.webm'

## => WSL2 環境だと上手くGPUを認識してくれず失敗
```
