PORTNAME=		mDNSResponder
PORTVERSION=	2600.140.3
PORTREVISION=	0
CATEGORIES=	net dns
MASTER_SITES=	https://opensource.apple.com/tarballs/mDNSResponder/ \
		https://github.com/apple-oss-distributions/mDNSResponder/archive/

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Apple's mDNSResponder
WWW=		https://developer.apple.com/bonjour/ \
		https://github.com/apple-oss-distributions/mDNSResponder

DISTNAME=	${PORTNAME}-${PORTVERSION}

USE_LDCONFIG=	YES
NO_PKG_REGISTER=yes

MAKE_ARGS+=	os=freebsd LOCALBASE=${PREFIX}
BUILD_WRKSRC=	${WRKSRC}/mDNSPosix
WRKSRC=		${WRKDIR}/mDNSResponder-mDNSResponder-${PORTVERSION}

USES=		compiler:c11 cpe gmake

CPE_VENDOR=	apple

# lib/libdns_sd.so.1 sbin/mdnsd
CONFLICTS_INSTALL?=	avahi-libdns openmdns

OPTIONS_DEFINE= IPV6


.include <bsd.port.pre.mk>

pre-build:
	@${REINPLACE_CMD} -e '/^CFLAGS_DEBUG/ s|-O0|${CFLAGS}|' ${WRKSRC}/mDNSPosix/Makefile
	@${REINPLACE_CMD} -e 's|\(#define IPV6_2292_PKTINFO \).*|\1IPV6_PKTINFO|; s|\(#define IPV6_2292_HOPLIMIT \).*|\1IPV6_HOPLIMIT|' ${WRKSRC}/mDNSPosix/mDNSUNP.h
	@${MV} ${WRKSRC}/mDNSShared/mDNSResponder.8 ${WRKSRC}/mDNSShared/mDNSResponderPosix.8

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mdnsresponder.in ${XIGMANAS_ROOTFS}/etc/rc.d/mdnsresponder
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/libdns_sd.so ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSClientPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSNetMonitor ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSProxyResponderPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mDNSResponderPosix ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${BUILD_WRKSRC}/build/prod/mdnsd ${XIGMANAS_ROOTFS}/usr/local/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"
.include <bsd.port.post.mk>
