FROM moodlehq/moodle-php-apache:8.2

# Enable Cron-job
RUN apt-get update && \
	apt-get --no-install-recommends install -y cron wget && \
	echo "*/5 * * * * runuser -u www-data -- php /var/www/html/admin/cli/cron.php" > /etc/cron.d/moodle

# Download moodle
RUN wget -O moodle.zip https://download.moodle.org/download.php/direct/stable404/moodle-4.4.1.zip && \
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
COPY --chown=www-data:www-data ./plugin/ /var/www/html/local/etutorsync/
COPY --chown=root:root ./docker/001-proxy.conf /etc/apache2/sites-available/

# Proxy (required for communication moodle-task admin in docker environment)
RUN a2enmod proxy && a2enmod proxy_http && a2enmod headers && a2ensite 001-proxy && echo 'Listen 8000' >> /etc/apache2/ports.conf