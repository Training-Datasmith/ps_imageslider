# syntax=docker/dockerfile:1
# verifier stage: PHPUnit PHAR acquisition and cryptographic verification
FROM --platform=linux/amd64 debian@sha256:bc49dc1918ee1a47a93e65b5e4676e8680fb754b133197b92ca52bfe6731d5f0 AS verifier
RUN apt-get update \
    && apt-get install -y --no-install-recommends ca-certificates curl gnupg \
    && rm -rf /var/lib/apt/lists/*
ENV PHPUNIT_VERSION=4.8.36
ENV PHPUNIT_SHA256=ab8bc3017a64cf75a2112f5e089a1d5b0c4cc2116556d60c1af5e3f584e85c5e
ENV PHPUNIT_FPR=D8406D0D82947747293778314AA394086372C20A
RUN set -eux; \
    GNUPGHOME="$(mktemp -d)"; chmod 0700 "$GNUPGHOME"; \
    export GNUPGHOME; \
    trap 'rm -rf "$GNUPGHOME"' EXIT; \
    curl -fsSL -o /tmp/phpunit.phar "https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar"; \
    curl -fsSL -o /tmp/phpunit.phar.asc "https://phar.phpunit.de/phpunit-${PHPUNIT_VERSION}.phar.asc"; \
    curl -fsSL -o /tmp/phpunit.key "https://keys.openpgp.org/vks/v1/by-fingerprint/${PHPUNIT_FPR}"; \
    gpg --batch --import /tmp/phpunit.key; \
    fpr="$(gpg --batch --with-colons --import-options show-only --import /tmp/phpunit.key | awk -F: '$1=="fpr" && $10!=""{print toupper($10); exit}')"; \
    test "$fpr" = "$PHPUNIT_FPR"; \
    gpg --batch --status-fd 1 --verify /tmp/phpunit.phar.asc /tmp/phpunit.phar 2>&1 | tee /tmp/gpg-out; \
    grep -q "^\\[GNUPG:\\] VALIDSIG ${PHPUNIT_FPR} " /tmp/gpg-out; \
    echo "${PHPUNIT_SHA256}  /tmp/phpunit.phar" | sha256sum -c -; \
    install -d /opt/phpunit; \
    install -m 0644 /tmp/phpunit.phar "/opt/phpunit/phpunit-${PHPUNIT_VERSION}.phar"

FROM --platform=linux/amd64 php@sha256:6ce95208609dc66df163ab936c970b3b34cd901b85c747102c5999f08ade9143
COPY --from=verifier /opt/phpunit/phpunit-4.8.36.phar /opt/phpunit/phpunit-4.8.36.phar
RUN docker-php-ext-install mysqli \
    && printf 'error_reporting=-1\ndisplay_errors=1\ndisplay_startup_errors=1\n' > /usr/local/etc/php/conf.d/error-reporting.ini
RUN set -eux; \
    php -d error_reporting=-1 -r 'if (PHP_VERSION !== "5.6.40") { fwrite(STDERR, "unexpected PHP version\n"); exit(1); }'; \
    php -d error_reporting=-1 -r 'if (!extension_loaded("mysqli")) { fwrite(STDERR, "mysqli missing\n"); exit(1); }'; \
    php -r 'if (error_reporting() !== -1) { fwrite(STDERR, "error_reporting is not -1\n"); exit(1); }'
WORKDIR /src
ENV PHPUNIT_PHAR=/opt/phpunit/phpunit-4.8.36.phar
