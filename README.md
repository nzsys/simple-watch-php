# Simple Watch

pingで生存確認はICMP拒否をしているサーバーに対して無効なのと、Webサーバーが生きていてもDBサーバーが死んでいるなどのケースがあるので、アクセス時にHTTP Status 200を返しているかを確認して通知するPHPを作成しました。

# Requirement

PHP: ^7.0
curl

# Usage

-host 確認したいURL

-hook 通知したいwebhookURL

```bash
# php simple.watch.php
usage: simple.watch.php [-host] url [[-hook] webhook url]
```

利用例: 毎分example.comのHTTP Statusを確認。異常・復旧を検知してslackに通知する。

```bash
# crontab -l
* * * * * /usr/local/bin/php simple.watch.php -host https://example.com -hook https://hooks.slack.com/services/xxx/xxx/xxx >> /tmp/simple.watch.log
```

おわり
