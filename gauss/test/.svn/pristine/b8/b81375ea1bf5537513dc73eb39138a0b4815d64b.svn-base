FROM alpine:3.8

RUN apk add --no-cache tzdata supervisor dcron incron ttf-dejavu && \
    apk add --no-cache php7 php7-mbstring php7-xml php7-simplexml php7-gd php7-openssl php7-curl php7-opcache php7-json php7-redis php7-pdo_mysql && \
    cp /usr/share/zoneinfo/Etc/GMT-8 /etc/localtime && echo 'Etc/GMT-8' > /etc/timezone && \
    apk del --no-cache tzdata && \
    mkdir -p /etc/supervisor.d /entrypoint.d
COPY entrypoint.sh /entrypoint.sh

EXPOSE 80
ENTRYPOINT [ "/entrypoint.sh" ]
