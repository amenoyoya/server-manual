#!/bin/bash

echo 'Rock-Paper-Scissors: じゃんけんゲーム'

# 無限ループ
while :
do
    # プレイヤー入力を促す
    ## echo -n: 後ろに改行をつけずにテキストを出力
    echo -n '手を入力 (0: グー, 1: チョキ, 2: パー): '
    ## read <変数名>: 標準入力を変数に格納
    read input

    # 入力値が 0, 1, 2 のいずれでもない場合はゲーム終了
    if [ $input != 0 ] && [ $input != 1 ] && [ $input != 2 ]; then
        break
    fi
    
    # コンピュータの手をランダムに選択: $((算術式)) を利用
    ## $RANDOM 環境変数: 0..32767 の乱数を出力
    ## $RANDOM を 3 で割った余り => 0..2 の乱数
    ## ※ bash の変数代入は「=」の前後にスペースを入れられないため注意
    com_input=$(($RANDOM % 3))

    # 数字 => 手 変換用配列
    hands=('グー' 'チョキ' 'パー')

    # 出した手を表示
    echo "あなたの手: ${hands[$input]}"
    echo "コンピュータの手: ${hands[$com_input]}"

    # 勝敗計算: $((プレイヤーの手 - コンピュータの手))
    ## 0 => あいこ
    ## -1 || 2 => プレイヤーの勝ち
    ## -2 || 1 => コンピュータの勝ち
    result=$(($input - $com_input))
    if [ $result = 0 ]; then
        echo 'あいこです'
    elif [ $result = -1 ] || [ $result = 2 ]; then
        echo 'あなたの勝ちです'
    else
        echo 'あなたの負けです'
    fi

    # 見やすさのため改行
    echo ''
done
