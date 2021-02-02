#!/bin/bash

export TZ=Asia/Tokyo

# サービス監視・復旧関数
function monitor() {
    local service="$1"
    local server_name="$2"
    local log_path="${3:-/var/log/service_monitor.log}"
    local slack_endpoint="$4"
    local slack_channel="${5:-#サーバ監視}"
    local slack_username="${6:-サーバ監視Bot}"
    local slack_emoji="${7:-:satellite_antenna:}"
    # 対象サービスが落ちていた場合復旧する
    if [ "$(/bin/systemctl status $service | /bin/grep running)" = "" ]; then
        # systemctl start $service の実行結果により、メッセージを変更
        local body=`/bin/systemctl start $service && echo "$service を再起動しました" || echo "$service の再起動に失敗しました"`
        local message="[ServerAlert: $server_name] $(/bin/date)\n$body\n"
        # ログ保存
        echo -e "$message" | tee -a "$log_path"
        # slack_endpoint が指定されている場合はSlack通知
        if [ "$slack_endpoint" != "" ]; then
            curl -X POST --data-urlencode "payload={\"channel\": \"$slack_chennel\", \"username\": \"$slack_username\", \"text\": \"$message\", \"icon_emoji\": \"$slack_emoji\"}" "$slack_endpoint" \
            && echo ''
        fi
    fi
}

for service in sshd httpd mysqld; do
    monitor $service centos6 /var/log/service_monitor.log https://hooks.slack.com/services/xxxx/xxxx/xxxx
done
