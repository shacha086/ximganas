PORTNAME=		iperf
PORTVERSION=    3.20
PORTREVISION=   0
CATEGORIES=	benchmarks

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Improved tool to measure TCP and UDP bandwidth
WWW=		https://software.es.net/iperf/

USE_GITHUB=	yes
GH_ACCOUNT=     esnet 
GH_TAGNAME=     0fa686e

USES=		cpe libtool ssl
GNU_CONFIGURE=	yes
GNU_CONFIGURE_MANPREFIX=	${PREFIX}/share
USE_LDCONFIG=	yes

CPE_VENDOR=	iperf_project

.include <bsd.port.options.mk>

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/iperf3 ${XIGMANAS_ROOTFS}${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/.libs/libiperf.so.0 ${XIGMANAS_ROOTFS}${PREFIX}/lib
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
