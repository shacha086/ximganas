PORTNAME=		openssh-portable
PORTVERSION=	1.0
PORTREVISION=	5
CATEGORIES=		security
COMMENT=		The portable version of OpenBSD's OpenSSH
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build
DISABLE_VULNERABILITIES=yes

BUILD_DEPENDS=	${NONEXISTENT}:security/openssh-portable

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/sshd ${XIGMANAS_ROOTFS}/usr/sbin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/ssh ${XIGMANAS_ROOTFS}/usr/bin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/sshd-auth ${XIGMANAS_ROOTFS}/usr/local/libexec
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/sshd-session ${XIGMANAS_ROOTFS}/usr/local/libexec
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/sftp-server ${XIGMANAS_ROOTFS}/usr/local/libexec
	@${CP} -pv ${PORTSDIR}/security/openssh-portable/work-default/openssh-*/moduli ${XIGMANAS_ROOTFS}/etc/ssh

# Create link to moduli file to prevent log entry:
# WARNING: /usr/local/etc/ssh/moduli does not exist, using fixed modulus
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/usr/local/etc/ssh
	@${LN} -fsv /etc/ssh/moduli ${XIGMANAS_ROOTFS}/usr/local/etc/ssh/moduli
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
