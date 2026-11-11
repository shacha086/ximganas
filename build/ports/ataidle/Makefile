PORTNAME=		ataidle
PORTVERSION=	2.7.2
CATEGORIES=		sysutils
COMMENT=		Utility to set spindown timeout for ATA drives
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=		http://www.cran.org.uk/bruce/software/


do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${XIGMANAS_ROOTFS}/etc/rc.d/${PORTNAME}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/ataidle ${XIGMANAS_ROOTFS}/usr/local/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>