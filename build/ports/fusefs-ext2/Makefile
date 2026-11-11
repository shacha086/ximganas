PORTNAME=	fusefs-ext2
DISTVERSIONPREFIX=	v
DISTVERSION=	0.0.11
PORTREVISION=	1
CATEGORIES=	filesystems

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	FUSE module to mount ext2, ext3 and ext4 with read write support
WWW=		https://github.com/alperakcan/fuse-ext2

LICENSE=	GPLv2
LICENSE_FILE=	${WRKSRC}/COPYING

BUILD_DEPENDS=	mke2fs:filesystems/e2fsprogs-core
RUN_DEPENDS=	mke2fs:filesystems/e2fsprogs-core

USES=		autoreconf fuse libtool localbase:ldflags pkgconfig
USE_GITHUB=	yes
GH_ACCOUNT=	alperakcan
GH_PROJECT=	fuse-ext2
GH_TAGNAME=	e8f1063

GNU_CONFIGURE=	yes

CPPFLAGS+=	-I${LOCALBASE}/include/e2fsprogs
LDFLAGS+=	-L${LOCALBASE}/lib/e2fsprogs -Wl,--rpath \
		-Wl,${LOCALBASE}/lib/e2fsprogs

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/fuse-ext2/fuse-ext2 ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/fuse-ext2/fuse-ext2.probe ${XIGMANAS_ROOTFS}/usr/local/bin
# XigmaNAS® End install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
