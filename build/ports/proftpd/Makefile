PORTNAME=		proftpd
PORTVERSION=	1.3.8d
PORTREVISION=	0
CATEGORIES=		ftp
COMMENT=		Highly configurable FTP daemon
WWW?=		http://www.proftpd.org/
MAINTAINER=		zoon01@xigmanas.com

MASTER_SITES=		ftp://ftp.proftpd.org/distrib/source/ \
						https://github.com/downloads/proftpd/proftpd.github.com/ \
						ftp://proftpd.networkedsystems.co.uk/distrib/source/ \
						http://mirrors.ibiblio.org/pub/mirrors/proftpd/distrib/source/ \
						http://www.mirrorservice.org/sites/ftp.proftpd.org/distrib/source/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}
EXTRACT_ONLY=	${DISTNAME}${EXTRACT_SUFX}

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=		yes
NO_LICENSES_DIALOGS=		yes
LICENSE=	GPLv2
#LICENSE_FILE=	${WRKSRC}/COPYING

CPE_VENDOR=	proftpd
CPE_PRODUCT=	proftpd

#MAKE_JOBS_UNSAFE=       yes
USES+=          gmake libtool
USE_AUTOTOOLS=	autoconf
GNU_CONFIGURE=	yes
USE_GETTEXT=		yes
USES+=          cpe ssl

MODULES=	mod_ratio:mod_ident:mod_readme:mod_rewrite:mod_site_misc:mod_tls:mod_wrap:mod_wrap2:mod_wrap2_file:mod_ban:mod_ctrls_admin:mod_ifsession:mod_sftp:mod_digest

OPTIONS+=       WRAP "Include mod_wrap2" on

CONFIGURE_ARGS=	--localstatedir=/var/run \
				--with-pkgconfig=libdata/pkgconfig \
				--sysconfdir=${PREFIX}/etc \
				--enable-dso \
				--disable-sendfile \
				--enable-ipv6 \
				--enable-nls \
				--with-modules=${MODULES} \
				--with-includes=${WORKDIR}/include:${LOCALBASE}/include \
				--with-libraries=${LOCALBASE}/lib \
				--enable-auth-pam \
				--enable-ctrls \
				--enable-largefile

CONFIGURE_ENV=	"LIBS=-lintl -L${LOCALBASE}/lib -lssl -lcrypto -L${OPENSSLLIB}"
CPPFLAGS+=      -DHAVE_OPENSSL -I${OPENSSLINC}

.include <bsd.port.pre.mk>

MODULES!=${ECHO} ${MODULES} | ${SED} -e 's,^:,,' -e 's,:$$,,'

post-patch:
	@${REINPLACE_CMD} -e 's|proftpd\.conf |proftpd.conf.sample |g' ${WRKSRC}/Makefile.in

pre-configure:
	@${ECHO_MSG} "==> Configuring with following modules:"
	@${ECHO_MSG} "${MODULES:C/^://g}"

post-configure:
	${REINPLACE_CMD} -e 's: -lnsl::' ${WRKSRC}/Make.rules
	${REINPLACE_CMD} -e 's:/usr/sbin:${PREFIX}/sbin:' \
		-e 's:/usr/bin:${PREFIX}/bin:' \
		${WRKSRC}/src/proftpd.8 ${WRKSRC}/utils/ftpshut.8 \
		${WRKSRC}/utils/ftpcount.1
pre-build:
	@cd ${WRKSRC} && ${MAKE_CMD} include/buildstamp.h

post-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc/link
	@${INSTALL_SCRIPT} -v ${FILESDIR}/${PORTNAME}.in ${XIGMANAS_ROOTFS}/etc/rc.d/${PORTNAME}
	@${LN} -fsv /var/etc/proftpd.conf ${XIGMANAS_ROOTFS}/usr/local/etc/proftpd.conf
# sbin
.for file in proftpd
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/${file} ${XIGMANAS_ROOTFS}/usr/local/sbin
.endfor
# bin
.for file in ftpdctl ftptop ftpwho
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/${file} ${XIGMANAS_ROOTFS}/usr/local/bin
.endfor
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>