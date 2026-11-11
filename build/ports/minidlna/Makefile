PORTNAME=		minidlna
PORTVERSION=	1.3.3
PORTREVISION=   8
PORTEPOCH=	1
CATEGORIES=	net multimedia www

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Media-server compatible with "Digital Life Network Alliance"
WWW=		http://minidlna.sourceforge.net

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=	yes
NO_LICENSES_DIALOGS=	yes
LICENSE=	GPLv2

CFLAGS+=	-I${LOCALBASE}/ffmpeg4/include
LIB_DEPENDS=	libsqlite3.so:databases/sqlite3 \
		libexif.so:graphics/libexif \
		libid3tag.so:audio/libid3tag \
		libogg.so:audio/libogg \
		libvorbis.so:audio/libvorbis \
		libFLAC.so:audio/flac \
 		libavformat.so:multimedia/ffmpeg4

GNU_CONFIGURE=	yes
CONFIGURE_ARGS=	--with-db-path=/var/db/minidlna \
		--with-os-url="https://www.xigmanas.com"

CPPFLAGS+=	-I${LOCALBASE}/ffmpeg4/include -I${LOCALBASE}/include
LDFLAGS+=	-L${LOCALBASE}/ffmpeg4/lib -L${LOCALBASE}/lib
CFLAGS+=	-DXigmaNAS
LIBS+=		-L${LOCALBASE}/lib
USES+=		autoreconf cpe gettext-tools iconv jpeg

USE_GITHUB=     yes
GH_ACCOUNT=     glebius
GH_TAGNAME=     967337d

CPE_VENDOR=	minidlna_project

PKGMESSAGE=	${WRKSRC}/pkg-message
SUB_FILES+=	pkg-message
USE_RC_SUBR=	minidlna
SUB_LIST+=	USER=${USERS}

USERS=	dlna
GROUPS=	dlna

OPTIONS_DEFINE=	DEBUG NLS
OPTIONS_SUB=	yes

DEBUG_CFLAGS=	-DDEBUG
NLS_USES=	gettext-runtime

.include <bsd.port.options.mk>

# XigmaNAS
NO_PKG_REGISTER=yes
ICONSDIR=	${MASTERDIR}/icons
XIGMANAS_ICONS=	${WRKSRC}/xigmanas-icons.c

post-configure:
	@echo "creating icon file..."
	@(cd ${ICONSDIR}; sh mkicons.sh > ${XIGMANAS_ICONS})

post-install:
	${INSTALL_MAN} ${WRKSRC}/*.5 ${STAGEDIR}${PREFIX}/share/man/man5/
	${INSTALL_MAN} ${WRKSRC}/*.8 ${STAGEDIR}${PREFIX}/share/man/man8/
	${INSTALL_DATA} ${WRKSRC}/minidlna.conf ${STAGEDIR}${PREFIX}/etc/minidlna.conf.sample
	@${INSTALL_SCRIPT} -v ${FILESDIR}/minidlna.in /usr/local/etc/rc.d/minidlna
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/minidlna.in ${XIGMANAS_ROOTFS}/etc/rc.d/minidlna
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/minidlnad ${XIGMANAS_ROOTFS}/usr/local/sbin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
