PORTNAME=		isboot
PORTVERSION=	0.3.4
PORTREVISION=   0
CATEGORIES=		net
COMMENT=		iSCSI boot driver by Daisuke Aoyama (aoyama@peach.ne.jp)

MAINTAINER=		zoon01@xigmanas.com
#MASTER_SITES=		http://www.peach.ne.jp/archives/isboot/

WRKSRC=                 ${WRKDIR}/${DISTNAME}/src
USES=			kmod
ONLY_FOR_ARCHS=		i386 amd64

.include <bsd.port.pre.mk>
.if ${ARCH} == "amd64"
CFLAGS+=	-DVIMAGE
.endif

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_KLD} -v ${WRKSRC}/isboot.ko ${XIGMANAS_ROOTFS}/boot/kernel
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
