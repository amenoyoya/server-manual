#!/bin/bash

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
    if [ "$(service $service status | grep running)" = "" ]; then
        # service $service start の実行結果により、メッセージを変更
        local body=`service $service start && echo "$service を再起動しました" || echo "$service の再起動に失敗しました"`
        # local message="[ServerAlert: $server_name] $(date)\n$body\n"
        local message="service $service status: $(service $service status)"
        # ログ保存
        echo "$message" | tee -a "$log_path"
        # slack_endpoint が指定されている場合はSlack通知
        if [ "$slack_endpoint" != "" ]; then
            curl -X POST --data-urlencode "payload={\"channel\": \"$slack_chennel\", \"username\": \"$slack_username\", \"text\": \"$message\", \"icon_emoji\": \"$slack_emoji\"}" "$slack_endpoint" \
            && echo ''
        fi
    else
        echo "$(service $service status)"
    fi
}

monitor sshd centos6 /var/log/service_monitor.log https://hooks.slack.com/services/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 '#test'
# monitor crond centos6 /var/log/service_monitor.log https://hooks.slack.com/services/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 '#test'
# monitor httpd centos6 /var/log/service_monitor.log https://hooks.slack.com/services/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 '#test'
# monitor mysqld centos6 /var/log/service_monitor.log https://hooks.slack.com/services/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 '#test'
