# シェルスクリプトTips

## ファイルベースデータベースのIDソート

ファイルベースデータベースとして、以下のようなフォーマットのファイルがあるとする

```
MPCPLAYLIST
ID,カラム名,カラム値
 :
```

実のところこれは Media Player Classic のプレイリストファイルだが、以下のようにIDの順番がおかしくなったプレイリストファイル（`target.mpcpl`）があるとする

```
MPCPLAYLIST
5,type,0
5,filename,C:\Users\user\Videos\video5.mp4
6,type,0
6,filename,C:\Users\user\Videos\video6.mp4
7,type,0
7,filename,C:\Users\user\Videos\video7.mp4
8,type,0
8,filename,C:\Users\user\Videos\video8.mp4
9,type,0
9,filename,C:\Users\user\Videos\video9.mp4
1,type,0
1,filename,C:\Users\user\Videos\video1.mp4
2,type,0
2,filename,C:\Users\user\Videos\video2.mp4
3,type,0
3,filename,C:\Users\user\Videos\video3.mp4
4,type,0
4,filename,C:\Users\user\Videos\video4.mp4
```

Excelなりスプレッドシートなり表計算ツールを使えば簡単だが、ここではあえてシェルスクリプトでIDをソートする

### 設計
上記ファイルで、IDを正しくソートするためには以下のような計算が必要になる

- 5～9 は -4 する
    - `5` => `1`
    - `6` => `2`
    - `7` => `3`
    - `8` => `4`
    - `9` => `5`
- 1～4 は +5 する
    - `1` => `6`
    - `2` => `7`
    - `3` => `8`
    - `4` => `9`

### awk を使った実装
上記のような複雑な計算・置換を必要とするファイル操作を行う際は awk を使うと楽

まずは1行目の `MPCPLAYLIST` のことは置いておいて、2行目以降のCSV形式部分について awk で区切り処理を行う

```bash
# awk -F '区切り文字' '{処理}' 対象ファイル
## print $1: 区切ったフィールドの内1番目を出力
$ awk -F ',' '{print $1}' target.mpcpl

MPCPLAYLIST
5
5
6
6
:
1
1
2
2
:
```

awk では通常のプログラミング言語と同等の計算処理および条件分岐を行うことができるため、`$1 が 5 以上の場合は ($1 - 4) を出力`、`$1 が 4 以下の場合は ($1 + 5) を出力` という処理を追加する

```bash
$ awk -F ',' '{
    if ($1 >= 5) {
        print $1 - 4
    } else {
        print $1 + 5
    }
}' target.mpcpl

-4 # <= MPCPLAYLIST は数値ではないためおかしくなるが一旦無視
1
1
2
2
:
9
9
```

これでひとまずIDをソートすることはできたので、2～3番目のフィールドも出力するように修正する

```bash
# awk では printf (書式付き出力) を使うと改行なしで出力できる
$ awk -F ',' '{
    if ($1 >= 5) {
        printf $1 - 4
    } else {
        printf $1 + 5
    }
    print "," $2 "," $3
}' target.mpcpl

-4,,
1,type,0
1,filename,C:\Users\user\Videos\video5.mp4
 :
9,type,0
9,filename,C:\Users\user\Videos\video4.mp4
```

最後に、1行目（`MPCPLAYLIST`）はそのまま出力するように変更する

awk では `NF` 変数にカレント行のフィールド数が格納されているため、`NF が 1 の場合は $0 (そのままの行) を出力` という処理を追加すれば完了である

```bash
$ awk -F ',' '{
    if (NF == 1) {
        print $0
    } else {
        if ($1 >= 5) {
            printf $1 - 4
        } else {
            printf $1 + 5
        }
        print "," $2 "," $3
    }
}' target.mpcpl

MPCPLAYLIST
1,type,0
1,filename,C:\Users\user\Videos\video5.mp4
 :
9,type,0
9,filename,C:\Users\user\Videos\video4.mp4
```

### ファイルへの保存
ファイルベースデータベースのIDソートシェルは完成である

後はファイルに保存するだけだが、シェルスクリプトの注意点として **処理中のファイルに上書き出力するとファイルの中身が消える** という仕様があるため要注意

基本的には「別のファイルに一時保存」→「元のファイルを削除」→「一時保存したファイル名を元のファイル名にリネーム」という手順を踏めば良いのだが、[一時ファイル無しで、ファイルを上書き更新するシェルスクリプト](https://qiita.com/richmikan@github/items/eb37998da9ba5e7f4df1) のワンライナーを使うと楽

```bash
# 一時ファイルなしで $file を上書き更新
$ (rm "$file" && CMD1 | CMD2 | ... > "file") < "$file"
```

よって、最終的なワンライナーは以下の通り

```bash
$ (rm target.mpcpl && awk -F ',' '{
    if (NF == 1) {
        print $0
    } else {
        if ($1 >= 5) {
            printf $1 - 4
        } else {
            printf $1 + 5
        }
        print "," $2 "," $3
    }
}' > target.mpcpl) < target.mpcpl
```
