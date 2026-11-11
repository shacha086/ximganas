PORTNAME=		istgt
PORTVERSION=		20180521
CATEGORIES=		net
COMMENT=		Implementation of userland ISCSI target
MAINTAINER=		aoyama@xigmanas.com

MASTER_SITES=		http://www.peach.ne.jp/archives/istgt/

.include <bsd.port.options.mk>
.if ${OSVERSION} < 1000000 || !defined(CC)
CC=clang
.endif

GNU_CONFIGURE=	yes
CONFIGURE_ARGS+= --with-logfacility="daemon" --with-logpriority="info"

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/iscsi_target.in ${XIGMANAS_ROOTFS}/etc/rc.d/iscsi_target
# bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/istgt ${XIGMANAS_ROOTFS}${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/istgtcontrol ${XIGMANAS_ROOTFS}${PREFIX}/bin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
