#!/bin/bash

# --- cron登録 ---

tee -a /var/spool/cron/root << \EOS
# サービス監視・復旧: 30秒ごと実行
* * * * * (sleep 30; echo "$(date) run monitor.sh"; /bin/bash /root/scripts/monitor.sh) > /var/log/cron_log_monitor.log

# バックアップ: 3日ごとの 03:00 に実行
0 3 */3 * * (echo "$(date) run backup.sh"; /bin/bash /root/scripts/backup.sh) > /var/log/cron_log_backup.log
EOS
