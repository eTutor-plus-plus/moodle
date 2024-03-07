FROM moodlehq/moodle-php-apache:8.2

# Enable Cron-job
RUN apt-get update && \
	apt-get --no-install-recommends install -y cron wget && \
	echo "*/5 * * * * runuser -u www-data -- php /var/www/html/admin/cli/cron.php" > /etc/cron.d/moodle

# Download moodle
RUN wget -O moodle.zip https://download.moodle.org/download.php/direct/stable403/moodle-4.3.3.zip && \
	unzip moodle.zip && \
	mv moodle/* /var/www/html && \
	rm -rf moodle.zip moodle

# Download coderunner
RUN wget -O coderunner.zip https://moodle.org/plugins/download.php/29972/qtype_coderunner_moodle43_2023090800.zip && \
	wget -O coderunner_qb.zip https://moodle.org/plugins/download.php/25541/qbehaviour_adaptive_adapted_for_coderunner_moodle43_2021112300.zip && \
	unzip coderunner.zip -d /var/www/html/question/type && \
	unzip coderunner_qb.zip -d /var/www/html/question/behaviour && \
	rm coderunner.zip coderunner_qb.zip

# Copy files
COPY --chown=www-data:www-data ./docker/defaults.php /var/www/html/local/
COPY --chown=www-data:www-data ./docker/configure_moodle.php /var/www/html/admin/cli/