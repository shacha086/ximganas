PORTNAME=		sipcalc
PORTVERSION=	1.1.6
CATEGORIES=		net-mgmt
COMMENT=		IP subnet calculator with IPv6 support
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=	http://www.routemeister.net/projects/sipcalc/files/

USE_GETOPT_LONG=	yes
GNU_CONFIGURE=	yes
NO_PKG_REGISTER=	yes

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/sipcalc ${XIGMANAS_ROOTFS}/usr/local/bin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>