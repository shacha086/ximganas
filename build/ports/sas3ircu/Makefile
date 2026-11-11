PORTNAME=		sas3ircu
PORTVERSION=	17.00.00.00
PORTREVISION=	0
CATEGORIES=		sysutils
MASTER_SITES=		# empty
DISTNAME=		SAS3IRCU_P16
COMMENT=		SAS-3 Integrated RAID Configuration Utility
MAINTAINER=		zoon01@xigmanas.com

USES=			zip

NO_BUILD=		yes
NO_WRKSRC=		yes

ONLY_FOR_ARCHS=		amd64
ONLY_FOR_ARCHS_REASON=	Binaries only available for i386 and amd64

.include <bsd.port.pre.mk>

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_PROGRAM} -v ${WRKSRC}/sas3ircu_rel/sas3ircu/sas3ircu_freebsd_${ARCH}_rel/sas3ircu ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>