PORTNAME=		inadyn
PORTVERSION=	2.12.0
PORTREVISION?=  1
CATEGORIES=	dns
COMMENT=	Dynamic DNS update client
MAINTAINER=	zoon01@xigmanas.com
WWW=		https://troglobit.com/projects/inadyn/

MASTER_SITES=	https://github.com/troglobit/${PORTNAME}/releases/download/v${PORTVERSION}/

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=		yes
NO_LICENSES_DIALOGS=		yes
LICENSE=			GPLv2
#LICENSE_FILE=			${WRKSRC}/COPYING

LIB_DEPENDS=			libconfuse.so:devel/libconfuse
RUN_DEPENDS=			ca_root_nss>=0:security/ca_root_nss

USES=				pkgconfig tar:xz

GNU_CONFIGURE=			yes
GNU_CONFIGURE_MANPREFIX=	${PREFIX}/share

USERS=				inadyn
GROUPS=				inadyn

USE_RC_SUBR=			inadyn

OPTIONS_DEFINE=			DOCS EXAMPLES
OPTIONS_DEFAULT=		OPENSSL
OPTIONS_SINGLE=			TLS
OPTIONS_SINGLE_TLS=		GNUTLS OPENSSL

GNUTLS_LIB_DEPENDS=		libgnutls.so:security/gnutls
OPENSSL_USES=			ssl
OPENSSL_CONFIGURE_ENABLE=	openssl
OPENSSL_CONFIGURE_ON=		OpenSSL_CFLAGS=-I${OPENSSLINC} OpenSSL_LIBS="-L${OPENSSLLIB} -lssl"

post-patch:
	@${REINPLACE_CMD} -e "s,%%PREFIX%%,${PREFIX},g" \
		${WRKSRC}/man/inadyn.8 ${WRKSRC}/man/inadyn.conf.5 \
		${WRKSRC}/examples/README.md ${WRKSRC}/examples/inadyn.conf

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc/link
	@${INSTALL_SCRIPT} -v ${FILESDIR}/inadyn.in ${XIGMANAS_ROOTFS}/etc/rc.d/inadyn
# sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/src/inadyn ${XIGMANAS_ROOTFS}/usr/local/sbin/inadyn
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
