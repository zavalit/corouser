FROM ubuntu:14.04
MAINTAINER Seva Dolgopolov "zavalit@gmail.com"

ENV REFRESHED_AT 2015-02-01

RUN apt-get update -yqq && apt-get -yqq install php5 php5-cli

ENV COROUSER_PORT 8081

EXPOSE 8081

WORKDIR /var/www

CMD ./bin/server $COROUSER_PORT


