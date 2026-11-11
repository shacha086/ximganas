PORTNAME=		rconf
PORTVERSION=	1.0.0
PORTREVISION=	0
CATEGORIES=		sysutils
COMMENT=		Command-line tool to manage content of rc.conf
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes

NO_WRKSUBDIR=	yes

NO_PKG_REGISTER=yes

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_SCRIPT} ${FILESDIR}/${PORTNAME} ${XIGMANAS_ROOTFS}/usr/local/sbin/
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"
.include <bsd.port.mk>