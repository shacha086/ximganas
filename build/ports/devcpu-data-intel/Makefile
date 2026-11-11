PORTNAME=	data
DISTVERSIONPREFIX=	microcode-
DISTVERSION=	20250812
PORTREVISION=	0
CATEGORIES=	sysutils
PKGNAMEPREFIX=	devcpu-
PKGNAMESUFFIX=	-intel

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Intel CPU microcode updates
WWW=		https://github.com/intel/Intel-Linux-Processor-Microcode-Data-Files

ONLY_FOR_ARCHS=	amd64

USES=		cpe
CPE_PART=	o
CPE_VENDOR=	intel
CPE_PRODUCT=	microcode
USE_GITHUB=	yes
GH_ACCOUNT=	intel
GH_PROJECT=	Intel-Linux-Processor-Microcode-Data-Files

DATADIR=	${PREFIX}/share/cpucontrol
FWDIR=		/boot/firmware
NO_ARCH=	yes

NO_WRKSUBDIR=	yes

post-extract:
	${CP} -p ${FILESDIR}/Makefile ${FILESDIR}/ucode-split.c ${WRKSRC}
	${REINPLACE_CMD} 's|%%GH_TAGNAME%%|${GH_TAGNAME}|g' ${WRKSRC}/Makefile
	${CAT} ${WRKSRC}/Intel-Linux-Processor-Microcode-Data-Files-${GH_TAGNAME}/intel-ucode/* \
	  ${WRKSRC}/Intel-Linux-Processor-Microcode-Data-Files-${GH_TAGNAME}/intel-ucode-with-caveats/* \
	  > ${WRKSRC}/intel-ucode.bin

do-install:
# default install stagedir
	${MKDIR} ${STAGEDIR}${DATADIR}/
	${INSTALL_DATA} ${WRKSRC}/mcodes/* ${STAGEDIR}${DATADIR}/
	${MKDIR} ${STAGEDIR}${FWDIR}
	${INSTALL_DATA} ${WRKSRC}/intel-ucode.bin ${STAGEDIR}${FWDIR}/

# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_SCRIPT} -v ${FILESDIR}/microcode_update.in ${XIGMANAS_ROOTFS}/etc/rc.d/microcode_update

	${MKDIR} ${XIGMANAS_ROOTFS}${DATADIR}/
	${INSTALL_DATA} ${WRKSRC}/mcodes/* ${XIGMANAS_ROOTFS}${DATADIR}/
	${MKDIR} ${XIGMANAS_ROOTFS}${FWDIR}
	${INSTALL_DATA} ${WRKSRC}/intel-ucode.bin ${XIGMANAS_ROOTFS}${FWDIR}/
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
