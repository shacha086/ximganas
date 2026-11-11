PORTNAME=		transmission
DISTVERSION=	4.1.0-beta.2
DISTVERSIONSUFFIX= +rac5c9e082d
PORTREVISION=	0
CATEGORIES=	net-p2p
MASTER_SITES=	https://github.com/${PORTNAME}/${PORTNAME}/releases/download/${DISTVERSION}/

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Transmission BitTorrent client
WWW=		https://www.transmissionbt.com

# XigmaNAS® contains license files
NO_LICENSES_INSTALL= yes
NO_LICENSES_DIALOGS= yes
LICENSE=	     GPLv3+
#LICENSE_FILE=	     ${WRKSRC}/COPYING

BUILD_DEPENDS=	${LOCALBASE}/include/fast_float/fast_float.h:math/fast_float \
		${LOCALBASE}/include/fmt/format.h:devel/libfmt \
		${LOCALBASE}/include/rapidjson/rapidjson.h:devel/rapidjson \
		${LOCALBASE}/include/utf8cpp/utf8.h:devel/utf8cpp

LIB_DEPENDS=	libb64.so:converters/libb64 \
		libcurl.so:ftp/curl \
		libdeflate.so:archivers/libdeflate \
		libdht.so:devel/jech-dht \
		libevent.so:devel/libevent \
		libminiupnpc.so:net/miniupnpc \
		libnatpmp.so:net/libnatpmp \
		libpsl.so:dns/libpsl \
		libutp.so:net-p2p/libutp

USES+=		cmake cpe iconv libtool pkgconfig ssl tar:xz

NO_PKG_REGISTER= yes

CPE_VENDOR=	transmissionbt
CPE_PRODUCT=	transmission

USE_OPENSSL=	yes
EXTRACT_SUFX=	.tar.xz
GNU_CONFIGURE=	yes
CPPFLAGS=	-I${LOCALBASE}/include
LDFLAGS=	-L${LOCALBASE}/lib
CONFIGURE_ENV=	CPPFLAGS="${CPPFLAGS}"
LDFLAGS+=	-pthread

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# dir
	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}
	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web/
	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
# script
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${XIGMANAS_ROOTFS}/etc/rc.d/${PORTNAME}
# bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/daemon/transmission-daemon ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/utils/transmission-create ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/utils/transmission-edit ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/utils/transmission-remote ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/utils/transmission-show ${XIGMANAS_ROOTFS}/usr/local/bin
# data
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/index.html ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/transmission-app.css ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/transmission-app.js ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/images/favicon.ico ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/images/favicon.png ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
	@${INSTALL_DATA} -v ${WRKSRC}/web/public_html/images/webclip-icon.png ${XIGMANAS_ROOTFS}/usr/local/share/${PORTNAME}/web/images
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"
.include <bsd.port.mk>

