PORTNAME=		tmux
PORTVERSION=	3.5a
PORTREVISION=	0
CATEGORIES=	sysutils
MASTER_SITES=	https://github.com/tmux/tmux/releases/download/${PORTVERSION}/
DISTFILES=	${DISTNAME}${EXTRACT_SUFX}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Terminal Multiplexer
WWW=		https://tmux.github.io/


LICENSE=	ISCL
LICENSE_FILE=	${WRKSRC}/COPYING

USES=		pkgconfig ncurses

USE_GITHUB=	nodefault
GH_TUPLE=	imomaliev:tmux-bash-completion:8da7f79:bash

GNU_CONFIGURE=	yes
GNU_CONFIGURE_MANPREFIX=${PREFIX}/share

NO_PKG_REGISTER=yes
CONFIGURE_ARGS=	--sysconfdir=${PREFIX}/etc --enable-utempter

PLIST_FILES=	bin/tmux \
		share/man/man1/tmux.1.gz \
		etc/bash_completion.d/tmux

PORTDOCS=	CHANGES README TODO
PORTEXAMPLES=	*

OPTIONS_DEFINE=	DOCS EXAMPLES LIBEVENT_STATIC BACKSPACE

LIBEVENT_STATIC_DESC=	 Build with static libevent
UTF8PROC_DESC=		 Build with utf8proc support
UTF8PROC_USES=		 localbase
BACKSPACE_DESC=		 Build with tty/keys patch

LIBEVENT_STATIC_LIB_DEPENDS_OFF= libevent.so:devel/libevent
LIBEVENT_STATIC_BUILD_DEPENDS= ${LOCALBASE}/lib/libevent.a:devel/libevent
LIBEVENT_STATIC_VARS= LESTATIC=${LOCALBASE}/lib/libevent.a

post-patch:
	@${REINPLACE_CMD} -e 's|/etc/tmux.conf|${PREFIX}/etc/tmux.conf|g' ${WRKSRC}/CHANGES \
		${WRKSRC}/tmux.h
	@${REINPLACE_CMD} -e 's|@SYSCONFDIR@|${PREFIX}/etc|g' ${WRKSRC}/tmux.1
post-configure-LIBEVENT_STATIC-on:
	${REINPLACE_CMD} -e '/LIBS/s|-levent[^[:blank:]]*|${LESTATIC}|' \
		${WRKSRC}/${MAKEFILE}

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/tmux ${XIGMANAS_ROOTFS}/bin
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>