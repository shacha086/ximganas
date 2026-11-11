PORTNAME=	php84-APCu
PORTREVISION=	0
CATEGORIES=	devel

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	APC User Caching

DISTFILES?=
NO_BUILD?=	         yes
NO_PKG_REGISTER?=	 yes
DEPENDS_TARGET=	clean build stage

DEP_STAGEDIR=	${PORTSDIR}/devel/pecl-APCu/work-php84/stage
BUILD_DEPENDS=	${NONEXISTENT}:devel/pecl-APCu

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${CP} -pv ${FILESDIR}/apcu.ini ${XIGMANAS_ROOTFS}${PREFIX}/etc/php

	@${MKDIR} ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20240924
	${INSTALL_DATA} ${DEP_STAGEDIR}/usr/local/lib/php/20240924/apcu.so ${XIGMANAS_ROOTFS}${PREFIX}/lib/php/extensions/no-debug-non-zts-20240924
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
