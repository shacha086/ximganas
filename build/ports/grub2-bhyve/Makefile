PORTNAME=		grub2-bhyve
PORTVERSION=		1.0
PORTREVISION=		1
CATEGORIES=		sysutils

COMMENT=		Grub-emu loader for bhyve
MAINTAINER=		zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:sysutils/grub2-bhyve
DEP_STAGEDIR=		${PORTSDIR}/sysutils/grub2-bhyve/work/stage

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/grub-bhyve ${XIGMANAS_ROOTFS}/usr/local/sbin
# XigmaNAS® End install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
