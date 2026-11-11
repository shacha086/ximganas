PORTNAME=		wol
PORTVERSION=	0.7.1
CATEGORIES=		net
COMMENT=		Tool to wake up Wake-On-LAN compliant computers
MAINTAINER=		aoyama@peach.ne.jp

MASTER_SITES=		SF/ahh/OldFiles

NO_PKG_REGISTER=yes
GNU_CONFIGURE=	yes
USE_PERL5_BUILD=yes
CPPFLAGS+=		-I${LOCALBASE}/include
LDFLAGS+=		-L${LOCALBASE}/lib

CONFIGURE_ARGS=		--disable-nls

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/wol ${XIGMANAS_ROOTFS}/${PREFIX}/bin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>