PORTNAME=		fusefs-ntfs
PORTVERSION=	1.0
PORTREVISION=	3
CATEGORIES=		filesystems
COMMENT=		Mount NTFS partitions and disk images
MAINTAINER=		zoon01@xigmanas.com

# No files are needed to be fetched.
DISTFILES?=
# No build is required for this port.
NO_BUILD?=	yes
# Don't register a port installation as a package.
NO_PKG_REGISTER?=	yes
# Set default target to execute when a port is calling a dependency.
#ALWAYS_BUILD_DEPENDS=	yes
DEPENDS_TARGET=	clean build stage

BUILD_DEPENDS=	${NONEXISTENT}:filesystems/ntfs

.include <bsd.port.pre.mk>

BUILD_DEPENDS+=	${NONEXISTENT}:filesystems/fusefs-libs \
		${NONEXISTENT}:devel/libublio

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/fusefs.in ${XIGMANAS_ROOTFS}/etc/rc.d/fusefs
# sbin
	@${CP} -pv /sbin/mount_fusefs ${XIGMANAS_ROOTFS}/sbin
# bin
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/filesystems/ntfs/work/ntfs-3g*/src/.libs/ntfs-3g ${XIGMANAS_ROOTFS}/usr/local/bin
# kernel
	@${CP} -pv /boot/kernel/fusefs.ko ${XIGMANAS_ROOTFS}/boot/kernel
# lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/filesystems/ntfs/work/ntfs-3g_ntfsprogs-2022.10.3/libntfs-3g/.libs/libntfs-3g.so ${XIGMANAS_ROOTFS}/usr/local/lib
	@${INSTALL_PROGRAM} -v ${PORTSDIR}/filesystems/ntfs/work/ntfs-3g*/libntfs-3g/.libs/libntfs-3g.so.89 ${XIGMANAS_ROOTFS}/usr/local/lib
# link
	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${XIGMANAS_ROOTFS}/sbin/mount_ntfs
	@${LN} -s -f -v /usr/local/bin/ntfs-3g ${XIGMANAS_ROOTFS}/sbin/mount_ntfs-3g
# XigmaNAS® End install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
