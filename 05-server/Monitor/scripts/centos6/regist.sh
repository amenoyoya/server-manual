#!/bin/bash

# --- cron登録 ---

tee -a /var/spool/cron/root << \EOS
# サービス監視・復旧: 30秒ごと実行
* * * * * sleep 30; /bin/bash /root/scripts/monitor.sh
EOS
