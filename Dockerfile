FROM ubuntu:14.04

RUN apt-get update
RUN apt-get -y upgrade
RUN apt-get install apache2 libapache2-mod-php5 php5-curl php5-memcached -y

RUN a2enmod php5
RUN a2enmod rewrite

ADD install/sensei-apply.conf /etc/apache2/sites-enabled/000-default.conf
ADD . /var/www/sensei-apply

EXPOSE 80

# Manually set the apache environment variables in order to get apache to work immediately.
ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid

# Execute the apache daemon in the foreground so we can treat the container as an
# executeable and it wont immediately return.
CMD ["/usr/sbin/apache2", "-D", "FOREGROUND"]
