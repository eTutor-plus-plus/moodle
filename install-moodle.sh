#!/bin/bash
runuser -u www-data -- php /var/www/html/admin/cli/install.php --lang=en --wwwroot="http://localhost:8000/" --dataroot=/var/www/moodledata --dbtype=mysqli --dbhost=etutor-moodle-db --dbname=moodle --dbuser=moodle --dbpass=moodleDbPwd --dbport=3306 --fullname="eTutor Test-Environment" --shortname="eTutor" --summary="Moodle for eTutor Test-Environment" --adminuser=admin --adminpass=secret --adminemail=etutor@example.com --non-interactive --agree-license

# Coderunner config
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=qtype_coderunner --name=default_penalty_regime --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=qtype_coderunner --name=jobe_host --set=etutor-moodle-jobe
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=qtype_coderunner --name=jobe_apikey --set=
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=qtype_coderunner --name=wsmaxhourlyrate --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=qtype_coderunner --name=wsmaxcputime --set=10

# Moodle config
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=tool_moodlenet --name=enablemoodlenet --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=enablewebservices --set=1
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=enableblogs --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=messaging --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=enableanalytics --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=timezone --set=Europe/Vienna
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=forcetimezone --set=Europe/Vienna
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=country --set=AT
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=defaultcity --set=Linz
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=forcelogin --set=1
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=cronclionly --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=cronremotepassword --set=secretCron
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=passwordpolicy --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --component=theme_boost --name=brandcolor --set=".navbar.navbar-light {background-color: #5ba755 !important;}"
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=smtphosts --set=etutor-moodle-mail:1026
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=noreplyaddress --set=etutor@example.com
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=enablewsdocumentation --set=1
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=debug --set=32767
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=cookiesecure --set=0
runuser -u www-data -- php /var/www/html/admin/cli/cfg.php --name=curlsecurityblockedhosts --set=""