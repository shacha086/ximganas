PORTNAME=		sas2ircu
PORTVERSION=	20.00.00.00
PORTREVISION=	1
CATEGORIES=		sysutils
MASTER_SITES=		https://docs.broadcom.com/docs-and-downloads/host-bus-adapters/host-bus-adapters-common-files/sas_sata_6g_p20_point6/
DISTNAME=		SAS2IRCU_P20
COMMENT=		Tool for basic management of mps(4) based LSI/Avago/Broadcom RAID controllers
MAINTAINER=		zoon01@xigmanas.com

USES=			zip

NO_BUILD=		yes
NO_WRKSRC=		yes

ONLY_FOR_ARCHS=		amd64
ONLY_FOR_ARCHS_REASON=	Binaries only available for i386 and amd64 from Broadcom

.include <bsd.port.pre.mk>

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_PROGRAM} -v ${WRKSRC}/sas2ircu_freebsd_${ARCH}_rel/sas2ircu ${XIGMANAS_ROOTFS}/${PREFIX}/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>