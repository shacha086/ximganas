PORTNAME=		ca_root_nss
PORTVERSION=		${VERSION_NSS}
PORTREVISION=		1
CATEGORIES=		security

COMMENT=		The root certificate bundle from the Mozilla Project
MAINTAINER=		zoon01@xigmanas.com

DISTFILES=
NO_BUILD=		yes
NO_PKG_REGISTER=	yes
#ALWAYS_BUILD_DEPENDS=	yes
DISABLE_VULNERABILITIES=yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=		${NONEXISTENT}:${PORTSDIR}/security/ca_root_nss
DEP_STAGEDIR=		${PORTSDIR}/security/ca_root_nss/work/stage

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/share/certs
	${INSTALL_DATA} -v ${DEP_STAGEDIR}${PREFIX}/share/certs/ca-root-nss.crt ${XIGMANAS_ROOTFS}${PREFIX}/share/certs
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/etc/ssl
	${LN} -fsv ${PREFIX}/share/certs/ca-root-nss.crt ${XIGMANAS_ROOTFS}${PREFIX}/etc/ssl/cert.pem.sample
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/openssl
	${LN} -fsv ${PREFIX}/share/certs/ca-root-nss.crt ${XIGMANAS_ROOTFS}${PREFIX}/openssl/cert.pem.sample
# ETCSYMLINK
	${LN} -fsv ${PREFIX}/share/certs/ca-root-nss.crt ${XIGMANAS_ROOTFS}/etc/ssl/cert.pem
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
