PORTNAME=	data
PORTVERSION=	20250729
PORTREVISION=	0
CATEGORIES=	sysutils
MASTER_SITES=	https://git.kernel.org/pub/scm/linux/kernel/git/firmware/linux-firmware.git/plain/amd-ucode/
PKGNAMEPREFIX=	devcpu-
PKGNAMESUFFIX=	-amd
DISTFILES=	microcode_amd.bin?id=${_REV} \
		microcode_amd_fam15h.bin?id=${_REV} \
		microcode_amd_fam16h.bin?id=${_REV} \
		microcode_amd_fam17h.bin?id=${_REV} \
		microcode_amd_fam19h.bin?id=${_REV} \
		microcode_amd_fam1ah.bin?id=${_REV}
DIST_SUBDIR=	${PKGNAME:C/_.*$//}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	AMD CPUs microcode updates

ONLY_FOR_ARCHS=	amd64

EXTRACT_CMD=	${CP}
EXTRACT_BEFORE_ARGS=	# empty
EXTRACT_AFTER_ARGS=	.
NO_ARCH=	yes
NO_BUILD=	yes

DATADIR=	${PREFIX}/share/cpucontrol
FWDIR=		/boot/firmware

NO_WRKSUBDIR=	yes
PLIST_FILES=	${DISTFILES:S/^/${DATADIR}\//g:C/\?.*//} \
		${FWDIR}/amd-ucode.bin

_REV=		3768c184de68a85b9df6697e7f93a2f61de90a99

post-extract:
	${CAT} ${WRKSRC}/microcode_amd* > ${WRKSRC}/amd-ucode.bin


do-install:
# default install stagedir
	${MKDIR} ${STAGEDIR}${DATADIR}/
	${MKDIR} ${STAGEDIR}${FWDIR}
	${INSTALL_DATA} ${WRKSRC}/amd-ucode.bin ${STAGEDIR}${FWDIR}/
.for f in ${DISTFILES}
	${INSTALL_DATA} ${WRKSRC}/${f} ${STAGEDIR}${DATADIR}/${f:C/\?.*//}
.endfor

# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_SCRIPT} -v ${FILESDIR}/microcode_update.in ${XIGMANAS_ROOTFS}/etc/rc.d/microcode_update

	${MKDIR} ${XIGMANAS_ROOTFS}${DATADIR}/
.for f in ${DISTFILES}
	${INSTALL_DATA} ${WRKSRC}/${f} ${XIGMANAS_ROOTFS}${DATADIR}/${f:C/\?.*//}
.endfor
	${MKDIR} ${XIGMANAS_ROOTFS}${FWDIR}
	${INSTALL_DATA} ${WRKSRC}/amd-ucode.bin ${XIGMANAS_ROOTFS}${FWDIR}/
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
