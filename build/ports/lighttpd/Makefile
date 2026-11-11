PORTNAME=	lighttpd
PORTVERSION=	1.4.82
PORTREVISION=	0
CATEGORIES=	www

COMMENT?=	A secure, fast, compliant, and very flexible Web Server
WWW?=		https://www.lighttpd.net/
MAINTAINER=	zoon01@xigmanas.com

MASTER_SITES=	https://download.lighttpd.net/lighttpd/releases-1.4.x/

LIB_DEPENDS+=	libpcre2-8.so:devel/pcre2 \
		libnettle.so:security/nettle \
		libsqlite3.so:${PORTSDIR}/databases/sqlite3 \
		libmemcached.so:${PORTSDIR}/databases/libmemcached \
		libbrotlicommon.so:archivers/brotli \
		libzstd.so:archivers/zstd

BUILD_DEPENDS=		libxml2>0:textproc/libxml2

GNU_CONFIGURE=	yes
USES=		autoreconf compiler:c11 gmake libtool pkgconfig tar:xz
USE_BZIP2=	yes

OPENSSL_USES=	ssl
OPENSSL_CONFIGURE_WITH=	openssl
OPENSSL_CONFIGURE_ON=	--with-openssl-includes=${OPENSSLINC} --with-openssl-libs=${OPENSSLLIB}

USE_GNOME+=	libxml2

CPPFLAGS+=	-I${LOCALBASE}/include
LDFLAGS+=	-L${LOCALBASE}/lib

.include <bsd.port.options.mk>

CONFIGURE_ARGS+=	--libdir=${PREFIX}/lib/lighttpd --enable-static \
			--sysconfdir=/var/etc/ \
			--enable-lfs \
			--enable-ipv6 \
			--without-mysql \
			--without-ldap \
			--with-openssl \
			--with-brotli \
			--without-lua \
			--without-bzip2 \
			--with-zstd \
			--with-webdav-props \
			--with-webdav-locks

do-install:

# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/lighttpd ${XIGMANAS_ROOTFS}/usr/local/sbin

	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_accesslog.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_ajp13.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_auth.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_authn_file.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_cgi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_deflate.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_dirlisting.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_extforward.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_h2.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_openssl.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_proxy.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_rrdtool.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_sockproxy.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_ssi.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_status.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_userdir.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_vhostdb.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_webdav.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/mod_wstunnel.so ${XIGMANAS_ROOTFS}/usr/local/lib/lighttpd
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
