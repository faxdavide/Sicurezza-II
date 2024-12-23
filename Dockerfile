FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt-get update && \
    apt-get -y install \
        iputils-ping \
        iproute2 \
        net-tools \
        dnsutils \
        vim \
        apache2 \
        php libapache2-mod-php \
        slapd ldap-utils \
        curl \
        php-ldap && \
    apt-get clean && \
    a2enmod ssl
    
CMD ["/bin/bash"]