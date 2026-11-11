PORTNAME=		sudo
PORTVERSION=		1.9.17p2
CATEGORIES=		security

COMMENT=		Allow others to run commands as root
MAINTAINER=		zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
#DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:security/sudo
DEP_STAGEDIR=		${PORTSDIR}/security/sudo/work-default/stage

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -m 4755 -v ${DEP_STAGEDIR}${PREFIX}/bin/sudo ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/sudoreplay ${XIGMANAS_ROOTFS}/usr/local/bin
	@${MKDIR} -p ${XIGMANAS_ROOTFS}/usr/local/libexec/sudo
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/libexec/sudo/sudo_intercept.so ${XIGMANAS_ROOTFS}/usr/local/libexec/sudo
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/sbin/visudo ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${LN} -fsv /usr/local/bin/sudo ${XIGMANAS_ROOTFS}/usr/local/bin/sudoedit
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/libexec/sudo ${XIGMANAS_ROOTFS}/usr/local/libexec
	@${INSTALL_DATA} -m 440 -v ${FILESDIR}/sudoers ${XIGMANAS_ROOTFS}/usr/local/etc
	@${MKDIR} -p ${XIGMANAS_ROOTFS}/usr/local/etc/pam.d
	@${INSTALL_DATA} -v ${DEP_STAGEDIR}${PREFIX}/etc/pam.d/sudo.default ${XIGMANAS_ROOTFS}/usr/local/etc/pam.d/sudo
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
