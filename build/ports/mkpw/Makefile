PORTNAME=		mkpw
PORTVERSION=		1.1
CATEGORIES=		sysutils
COMMENT=		Password generation tool
MAINTAINER=		zoon01@xigmanas.com

DISTFILES?=
NO_PKG_REGISTER=	yes

do-build:
	@${MKDIR} -p ${WRKSRC}
	@${TAR} -xvf ${FILESDIR}/${PORTNAME}.tar -C ${WRKSRC}
	@${MAKE} -C ${WRKSRC}

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME} ${XIGMANAS_ROOTFS}/usr/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
