PORTNAME=		arcconf
DISTVERSION=	5.03.00.${BUILD}
PORTREVISION=	1
CATEGORIES=	sysutils
MASTER_SITES=	https://storage.microsemi.com/raid/storage_manager/
DISTNAME=	arcconf_B${BUILD}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Adaptec SCSI/SAS RAID administration tool
WWW=		https://storage.microsemi.com/en-us/support/

# XigmaNAS® contains license files
NO_LICENSES_INSTALL= yes
NO_LICENSES_DIALOGS= yes
LICENSE=	Microsemi
LICENSE_NAME=	Microsemi License
LICENSE_FILE=	${WRKSRC}/freebsd${DIST_TYPE}/cmdline/License.txt
LICENSE_PERMS=	no-pkg-sell no-dist-sell no-dist-mirror auto-accept

ONLY_FOR_ARCHS=	amd64
ONLY_FOR_ARCH_REASON=	precompiled binary
LIB_DEPENDS+=    libstdc++.so.6:lang/gcc${GCC_DEFAULT}

USES=		zip
USE_GCC=        yes
BUILD=		27960

.include <bsd.port.pre.mk>

DIST_TYPE=	14_x86_64
NO_BUILD=	yes
NO_WRKSUBDIR=	yes

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${INSTALL_PROGRAM} -v ${WRKSRC}/freebsd${DIST_TYPE}/cmdline/arcconf ${XIGMANAS_ROOTFS}${PREFIX}/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
