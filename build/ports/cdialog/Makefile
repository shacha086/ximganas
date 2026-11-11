PORTNAME=			cdialog
PORTVERSION=	1.3.20251001
PORTEPOCH=	0
CATEGORIES=			devel
MASTER_SITES=	https://invisible-island.net/archives/${PORTNAME:S|^c||}/\
			https://invisible-mirror.net/archives/${PORTNAME:S|^c||}/
DISTNAME=	${PORTNAME:S|^c||}-${PORTVERSION:R}-${PORTVERSION:E}

COMMENT=			An enhanced version of 'dialog' to work with ncurses
MAINTAINER=		zoon01@xigmanas.com
WWW=		https://invisible-island.net/dialog/

EXTRACT_SUFX=	.tgz
NO_PKG_REGISTER=yes
USES=           libtool:build
GNU_CONFIGURE=		yes
GNU_CONFIGURE_MANPREFIX=${PREFIX}/share
USE_LDCONFIG=			yes
CONFIGURE_ARGS=		--enable-widec \
		--includedir=${PREFIX}/include/${PORTNAME} \
			--with-libtool \
			--with-ncursesw \
			--with-package=${PORTNAME}
MAKEFILE=	makefile

post-patch:
	@${FIND} ${WRKSRC}/samples/ -type f -exec ${REINPLACE_CMD} -i "" "s|dialog|cdialog|g" {} \;

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/cdialog ${XIGMANAS_ROOTFS}${PREFIX}/bin/cdialog
	@${INSTALL_PROGRAM} -v ${WRKSRC}/.libs/libcdialog.so.* ${XIGMANAS_ROOTFS}${PREFIX}/lib
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>