FROM alpine:3.19 AS builder

RUN apk update && \
    apk add \
        autoconf \
        cmake \
        curl \
        gcc \
        g++ \
        make \
        curl-dev \
        bzip2-dev \
        freetype-dev \
        libjpeg-turbo-dev \
        libpng-dev \
        libxml2-dev \
        linux-headers \
        oniguruma-dev \
        perl \
        sqlite-dev

ARG THREADS=1
ARG TESTS_ENABLE=0

WORKDIR /opt
ARG OPENSSL_VERSION=1.1.1w
ARG OPENSSL_CHECKSUM=cf3098950cb4d853ad95c0841f1f9c6d3dc102dccfcacd521d93925208b76ac8
RUN curl -O -L https://www.openssl.org/source/old/${OPENSSL_VERSION::-1}/openssl-${OPENSSL_VERSION}.tar.gz && \
    echo $OPENSSL_CHECKSUM  openssl-${OPENSSL_VERSION}.tar.gz | sha256sum -c && \
    tar -zxvf openssl-${OPENSSL_VERSION}.tar.gz && \
    rm -f openssl-${OPENSSL_VERSION}.tar.gz

WORKDIR openssl-${OPENSSL_VERSION}
RUN ./config shared \
        --prefix=/usr \
        --openssldir=/usr/local/openssl && \
    make -j $THREADS && \
    if [ $TESTS_ENABLE = 1 ]; then make test; fi && \
    make install

WORKDIR /opt
ARG PHP_VERSION=7.4.33
ARG PHP_CHECKSUM=5a2337996f07c8a097e03d46263b5c98d2c8e355227756351421003bea8f463e
RUN curl -O -L https://www.php.net/distributions/php-${PHP_VERSION}.tar.gz && \
    echo $PHP_CHECKSUM  php-${PHP_VERSION}.tar.gz | sha256sum -c && \
    tar -zxvf php-${PHP_VERSION}.tar.gz && \
    rm -f php-${PHP_VERSION}.tar.gz

WORKDIR php-${PHP_VERSION}
RUN ./configure \
        --with-config-file-path=/etc/php \
        --sysconfdir=/etc/php \
        --disable-all \
        --disable-cgi \
        --with-bz2 \
        --with-curl \
        --with-freetype \
        --with-iconv \
        --with-jpeg \
        --with-libxml \
        --with-mysqli \
        --with-openssl=/usr/local/openssl \
        --with-system-ciphers \
        --enable-cli \
        --enable-gd \
        --enable-phpdbg=no \
        --enable-fpm \
        --enable-json \
        --enable-mbstring \
        --enable-opcache \
        --enable-session \
        --enable-sockets \
        --enable-xml \
        --with-zlib && \
    make -j $THREADS && \
    if [ $TESTS_ENABLE = 1 ]; then make test; fi && \
    make install

FROM alpine:3.19

ENV TZ=Europe/Moscow

ARG OPENSSL_VERSION=1.1.1w
ARG BIN_DIR=/usr/local/bin
ARG SBIN_DIR=/usr/local/sbin
ARG EXT_DIR=/usr/local/lib/php/extensions/no-debug-non-zts-20190902
ARG SSL_DIR=/opt/openssl-${OPENSSL_VERSION}

COPY --from=builder $BIN_DIR $BIN_DIR
COPY --from=builder $SBIN_DIR $SBIN_DIR
COPY --from=builder $EXT_DIR $EXT_DIR
COPY --from=builder $SSL_DIR/libcrypto.so.1.1 $SSL_DIR/libssl.so.1.1 /usr/local/lib/

RUN apk update && \
    apk add --no-cache \
        bash \
        ca-certificates \
        curl \
        dumb-init \
        nginx \
        supervisor \
        freetype \
        libbz2 \
        libcurl \
        libjpeg \
        libpng \
        libxml2 \
        oniguruma \
        supercronic \
        tzdata

ARG UID=200
RUN adduser -u $UID -D -G www-data php

ADD --chown=php:www-data ./ /var/www/

ARG CFG_DIR=/var/www/_install/conf
RUN mkdir /etc/php && \
    mkdir /var/run/php && \
    chown php:www-data /var/run/php && \
    mkdir /var/log/php && \
    ln -sf /dev/stdout /var/log/php/php-fpm.log && \
    chown -R php:www-data /var/log/php && \
    mv $CFG_DIR/php* /etc/php/ && \
    mv $CFG_DIR/supervisord.conf /etc/ && \
    mv $CFG_DIR/nfkstats-nginx.conf /etc/nginx/http.d/ && \
    mv $CFG_DIR/entrypoint.sh /var/www/ && \
    ln -sf /dev/stdout /var/log/nginx/access.log && \
    ln -sf /dev/stdout /var/log/nginx/error.log && \
    rm -f /etc/nginx/http.d/default.conf && \
    rm -rf /var/www/localhost && \
    echo "0 * * * * /var/www/cron/update_graphs.sh" > /etc/crontab

ENV PORT=80
ENV REAL_IP="" 

HEALTHCHECK --start-period=20s --interval=30s --retries=2 CMD curl --fail http://localhost:$PORT || kill 1

WORKDIR /var/www

CMD ["/usr/bin/dumb-init", "--", "./entrypoint.sh"]
