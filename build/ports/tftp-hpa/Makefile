PORTNAME=		tftp-hpa
PORTVERSION=	5.2
PORTREVISION=	2
CATEGORIES=		ftp
MASTER_SITES=	KERNEL_ORG/software/network/tftp/tftp-hpa

COMMENT=		Advanced TFTP server
MAINTAINER=		zoon01@xigmanas.com

USES=		gmake tar:xz
GNU_CONFIGURE=	yes
GNU_CONFIGURE_MANPREFIX=${PREFIX}/share
USE_RC_SUBR=	tftpd

CONFIGURE_ARGS+=	--enable-largefile

post-patch:
	@${REINPLACE_CMD} -e 's,SBINDIR,LIBEXECDIR,; s,sbindir,libexecdir,' \
	    ${WRKSRC}/MCONFIG.in
	@${REINPLACE_CMD} -e 's,SBINDIR,LIBEXECDIR,g' \
	    ${WRKSRC}/tftpd/Makefile
	@${REINPLACE_CMD} -e 's,LOG_DAEMON,LOG_FTP,g' \
	    ${WRKSRC}/tftpd/tftpd.c

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/tftpd.in ${XIGMANAS_ROOTFS}/etc/rc.d/tftpd
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tftpd/tftpd ${XIGMANAS_ROOTFS}/usr/local/libexec/tftpd
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>