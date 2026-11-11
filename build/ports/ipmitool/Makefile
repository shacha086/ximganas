PORTNAME=		ipmitool
PORTVERSION=	1.0
PORTREVISION=	1
CATEGORIES=	sysutils

COMMENT=	CLI tool for IPMI-enabled devices
MAINTAINER=	zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=		clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/sysutils/ipmitool
DEP_STAGEDIR=		${PORTSDIR}/sysutils/ipmitool/work/stage

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/ipmitool ${XIGMANAS_ROOTFS}/usr/local/bin
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/share/ipmitool
	${INSTALL_DATA} ${DEP_STAGEDIR}${PREFIX}/share/ipmitool/enterprise-numbers ${XIGMANAS_ROOTFS}${PREFIX}/share/ipmitool
	${INSTALL_DATA} ${DEP_STAGEDIR}${PREFIX}/share/ipmitool/oem_ibm_sel_map ${XIGMANAS_ROOTFS}${PREFIX}/share/ipmitool
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>