PORTNAME=		wsdd
DISTVERSIONPREFIX= v
DISTVERSION=	0.9.0
PORTREVISION=	0
CATEGORIES=	net python
PKGNAMEPREFIX=	${PYTHON_PKGNAMEPREFIX}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Web Service Discovery (WSD) host daemon for Samba

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=	yes
NO_LICENSES_DIALOGS=	yes
LICENSE=	MIT

USES=		python:3.5+,run shebangfix
USE_GITHUB=	yes
GH_ACCOUNT=	christgau
GH_TAGNAME=	e9325b5
SHEBANG_FILES=	src/${PORTNAME}.py
NO_ARCH=	yes
NO_BUILD=	yes

USERS=		_wsdd
GROUPS=		_wsdd

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc/link
	@${INSTALL_SCRIPT} -v ${FILESDIR}/wsdd.in ${XIGMANAS_ROOTFS}/etc/rc.d/wsdd
#bin
	@${INSTALL_SCRIPT} -v ${WRKSRC}/src/${PORTNAME}.py ${XIGMANAS_ROOTFS}/usr/local/bin/${PORTNAME}
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
