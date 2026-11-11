PORTNAME=		php84
PORTVERSION=	8.4.14
PORTREVISION?=	0
CATEGORIES?=		lang
COMMENT=		PHP Scripting Language
WWW=			https://www.php.net/
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=		${MASTER_SITE_PHP:S,$,:release,}
MASTER_SITE_SUBDIR=	distributions/:release
DISTNAME=		php-${PORTVERSION:S/.r/RC/}
DISTFILES=			${DISTNAME}${EXTRACT_SUFX}:release
USES+=			compiler:c11 cpe gnome localbase pkgconfig tar:xz
CPE_PRODUCT=		php
PHP_VER=	84
IGNORE_WITH_PHP=	81 82 83 85
CONFLICTS_INSTALL?=	php[0-9][0-9]${PKGNAMESUFFIX}

NO_OPTIONS_SORT=	yes

BUILD_DEPENDS=		re2c>0:devel/re2c
LIB_DEPENDS=		libargon2.so:security/libargon2 \
			libpcre2-8.so:devel/pcre2

LIB_DEPENDS+=		libcurl.so:ftp/curl
LIB_DEPENDS+=		libmcrypt.so:security/libmcrypt
LIB_DEPENDS+=		libonig.so:devel/oniguruma
LIB_DEPENDS+=		libfreetype.so:print/freetype2
LIB_DEPENDS+=		libpng.so:graphics/png
LIB_DEPENDS+=		libjpeg.so:graphics/jpeg-turbo
LIB_DEPENDS+=		libvpx.so:multimedia/libvpx
LIB_DEPENDS+=		libgmp.so:math/gmp
LIB_DEPENDS+=		libc-client.so.2010:mail/panda-cclient
LIB_DEPENDS+=		libicui18n.so:devel/icu
LIB_DEPENDS+=		libzip.so:archivers/libzip

GNU_CONFIGURE=	yes
CPPFLAGS+=	-DU_USING_ICU_NAMESPACE=1
CONFIGURE_ENV=	CPPFLAGS="-I${LOCALBASE}/include"
USES+=          autoreconf:build ssl

AUTOTOOLSFILES=	 scripts/phpize.in
USE_GNOME=	 libxml2
USE_OPENLDAP=	 yes
NO_PKG_REGISTER= yes

LDFLAGS+=	-L${OPENSSLLIB} -lcrypto -lssl
# PR230207 Allow relocations against read-only segments (override lld default)
LDFLAGS_i386+=	-Wl,-z,notext

CONFIGURE_ARGS+=	\
		--with-pear \
		--with-openssl \
		--with-imap-ssl=${OPENSSLBASE} \
		--with-imap=${LOCALBASE} \
		--with-gettext \
		--with-external-pcre \
		--with-libxml \
		--with-mysqli \
		--with-pdo-mysql=mysqlnd \
		--with-zlib-dir=/usr \
		--with-config-file-path=/usr/local/etc \
		--with-config-file-scan-dir=/usr/local/etc/php \
		--enable-pdo \
		--with-pdo-sqlite=${LOCALBASE} \
		--with-sqlite3 \
		--with-iconv=${LOCALBASE} \
		--with-bz2=/usr \
		--with-curl \
		--enable-exif \
		--enable-gd \
		--with-freetype \
		--with-jpeg \
		--enable-intl \
		--with-ldap=${LOCALBASE} --with-ldap-sasl=${LOCALBASE} \
		--enable-mbstring \
		--with-gmp=${LOCALBASE} \
		--enable-bcmath \
		--with-mhash \
		--enable-ftp \
		--enable-soap \
		--enable-session \
		--enable-sockets \
		--enable-sysvmsg \
		--enable-sysvsem \
		--enable-sysvshm \
		--enable-tokenizer \
		--enable-simplexml \
		--with-zip \
		--with-zlib=/usr \
		--enable-opcache \

OPTIONS=	CGI "Build CGI version" on \
		CLI "Build CLI version" on \
		EMBED "Build embedded library" off \
		FPM "Build FPM version" off \
		LINKTHR "Link thread lib (for threaded extensions)" on \
		MYSQLND "Build with MySQL Native Driver)" on \
		IPV6 "Enable ipv6 support" on \
		APACHE "Build Apache module" off \
		AP2FILTER " Use Apache 2.x filter interface (experimental)" off \
		DEBUG "Enable debug" off \
		DTRACE "Enable DTrace support" off \
		PHPDBG "Enable debug" off \
		ZTS "Force Zend Thread Safety (ZTS) build" off

CONFIGURE_ENV+=	ac_cv_decimal_fp_supported="no" \
		lt_cv_path_SED="sed" \
		OPENSSL_CFLAGS="-I${OPENSSLINC}" \
		OPENSSL_LIBS="-L${OPENSSLLIB} -lssl -lcrypto" \
		PHP_OPENSSL=yes

post-patch:
	@${TOUCH} ${WRKSRC}/ext/php_config.h

pre-configure:
	@(cd ${WRKSRC} && ${SETENV} MAKE=${MAKE_CMD} ./buildconf --force)

post-build:
	@${ECHO_CMD} "PHP_VER=84" > ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_VERSION=${PORTVERSION}" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_SAPI=${PHP_SAPI}" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} "PHP_EXT_INC=hash pcre spl" >> ${WRKDIR}/php.conf
	@${ECHO_CMD} -n "PHP_EXT_DIR=" >> ${WRKDIR}/php.conf
	@${SH} ${WRKSRC}/scripts/php-config --extension-dir | ${SED} -ne 's,^${PREFIX}/lib/php/,,p' >> ${WRKDIR}/php.conf

test: build
	@(cd ${WRKSRC} && ${MAKE} test)

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/php-cgi-websrv ${XIGMANAS_ROOTFS}${PREFIX}/bin/
	@${CP} -pv ${FILESDIR}/php.ini ${XIGMANAS_ROOTFS}${PREFIX}/etc

	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cgi/php-cgi ${XIGMANAS_ROOTFS}${PREFIX}/bin/
	@${INSTALL_PROGRAM} -v ${WRKSRC}/sapi/cli/php ${XIGMANAS_ROOTFS}${PREFIX}/bin/
	@${INSTALL_PROGRAM} -v /usr/local/bin/gettext ${XIGMANAS_ROOTFS}${PREFIX}/bin/

	@${MKDIR} ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20240924
	${INSTALL_DATA} ${WRKSRC}/ext/opcache/.libs/opcache.so ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20240924
	@${CP} -pv ${FILESDIR}/opcache.ini ${XIGMANAS_ROOTFS}${PREFIX}/etc/php
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
