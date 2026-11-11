PORTNAME=	opie
PORTVERSION=	1.20230501
PORTREVISION=	0
CATEGORIES=	security

MAINTAINER=	zoon01@zoonsnas.nl
COMMENT=	One-time Passwords In Everything

USES=		uidfix
USE_GITHUB=	yes
GH_ACCOUNT=	freebsd
GH_PROJECT=	opie

MAKE_ARGS=	BINDIR=${PREFIX}/bin \
		INCLUDEDIR=${PREFIX}/include \
		LIBDIR=${PREFIX}/lib \
		MANDIR=${PREFIX}/share/man/man

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# lib
	@${INSTALL_PROGRAM} -v ${WRKSRC}/lib/libopie/libopie.so.8 ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${INSTALL_PROGRAM} -v ${WRKSRC}/pam/pam_opie/pam_opie.so.6 ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${INSTALL_PROGRAM} -v ${WRKSRC}/pam/pam_opieaccess/pam_opieaccess.so.6 ${XIGMANAS_ROOTFS}${PREFIX}/lib
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
