PORTNAME=		exfat
DISTVERSIONPREFIX= v
PORTVERSION=	   1.4.0
PORTREVISION=	   0
CATEGORIES=	   filesystems
PKGNAMEPREFIX=	fusefs-

COMMENT=	Full-featured exFAT FS implementation as a FUSE module
WWW=		https://github.com/relan/exfat
MAINTAINER=	zoon01@xigmanas.com

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=	yes
NO_LICENSES_DIALOGS=	yes
LICENSE=		GPLv2

LIB_DEPENDS=	libublio.so:devel/libublio

USE_GITHUB=     yes
GH_ACCOUNT=     relan

USES=		autoreconf fuse
GNU_CONFIGURE=	yes
CPPFLAGS+=	-I${LOCALBASE}/include -DUSE_UBLIO
LDFLAGS+=	-L${LOCALBASE}/lib
LIBS+=		-lublio

do-install:
	${INSTALL_PROGRAM} ${WRKSRC}/fuse/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin
	${INSTALL_PROGRAM} ${WRKSRC}/fsck/exfatfsck ${STAGEDIR}${PREFIX}/bin
	${LN} -fs ${PREFIX}/bin/mount.exfat-fuse ${STAGEDIR}${PREFIX}/bin/mount.exfat

# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/mount.exfat-fuse ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/exfatfsck ${XIGMANAS_ROOTFS}/usr/local/bin
	@${LN} -fsv ${PREFIX}/bin/mount.exfat-fuse ${XIGMANAS_ROOTFS}/usr/local/bin/mount.exfat
# XigmaNAS® End install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
