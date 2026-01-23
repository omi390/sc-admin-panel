#!/bin/bash
(crontab -l | grep -v "/usr/bin/php /home/u893549052/domains/myservicecart.com/public_html/panel/artisan email:send-renewal-reminder") | crontab -
(crontab -l; echo "0 0 * * * /usr/bin/php /home/u893549052/domains/myservicecart.com/public_html/panel/artisan email:send-renewal-reminder") | crontab -
