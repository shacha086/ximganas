PORTNAME=		rsync
PORTVERSION=	3.4.1
PORTREVISION=	3
CATEGORIES=		net
MAINTAINER=		zoon01@xigmanas.com
COMMENT=		A network file distribution/sync utility
WWW: 			http://rsync.samba.org/

MASTER_SITES=	https://www.mirrorservice.org/sites/rsync.samba.org/src/ \
		http://rsync.mirror.garr.it/src/ \
		http://www.mirrorservice.org/sites/rsync.samba.org/src/ \
		https://download.samba.org/pub/rsync/src/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}

EXTRA_PATCHES=	${FILESDIR}/extrapatch-main.c

LIB_DEPENDS=	libxxhash.so:devel/xxhash


GNU_CONFIGURE=	yes
GNU_CONFIGURE_MANPREFIX=${PREFIX}/share
NO_PKG_REGISTER=yes
CONFIGURE_ARGS+=	--disable-debug \
					--enable-ipv6 \
					--with-rsyncd-conf=/var/etc/rsyncd.conf \
					--with-included-popt \
					--enable-largefile

# Enable iconv support.
ICONV_USES=	iconv
CFLAGS+=		-I${LOCALBASE}/include
LDFLAGS+=		-L${LOCALBASE}/lib
CONFIGURE_ENV=	CFLAGS="${CFLAGS}" \
					LDFLAGS="${LDFLAGS}"

# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly).
FORCE_PKG_REGISTER=	yes

.include <bsd.port.pre.mk>

# guard against CFLAGS damage by MAKE_ENV
post-patch:
	@${REINPLACE_CMD} -e 's|CFLAGS|CONFIGUREDCFLAGS|' \
		-e 's|perl.*|${DO_NADA}|' \
			${WRKSRC}/Makefile.in


pre-configure:
	@${REINPLACE_CMD} -e 's:/etc/:${PREFIX}/etc/:g'  \
		${WRKSRC}/rsync.h ${WRKSRC}/rsync.1 ${WRKSRC}/rsyncd.conf.5
	@${REINPLACE_CMD} -e 's|malloc.h|stdlib.h|g' \
		${WRKSRC}/rsync.h ${WRKSRC}/zlib/zutil.h

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/rsync ${XIGMANAS_ROOTFS}/usr/local/bin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"
# Install on host system too, otherwise depending ports fail during ./configure process (e.g. scponly). 
	@${INSTALL_PROGRAM} -v ${WRKSRC}/rsync ${PREFIX}/bin

.include <bsd.port.post.mk>