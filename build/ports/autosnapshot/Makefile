PORTNAME=		autosnapshot
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Auto Snapshot tool
MAINTAINER=		zoon01@xigmanas.com

NO_PKG_REGISTER=yes

DISTFILES=
NO_BUILD=	yes
NO_WRKSUBDIR=	yes

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot ${XIGMANAS_ROOTFS}${PREFIX}/sbin
	@${INSTALL_SCRIPT} -v ${FILESDIR}/autosnapshot.in ${XIGMANAS_ROOTFS}/etc/rc.d/autosnapshot
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>