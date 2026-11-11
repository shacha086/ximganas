PORTNAME=		syncthing
PORTVERSION=	2.0.11
PORTREVISION=	0
DISTVERSIONPREFIX= v
CATEGORIES=	net

COMMENT=	Encrypted file sync tool
MAINTAINER=	zoon01@xigmanas.com
WWW=		https://syncthing.net/

MASTER_SITES=	https://github.com/syncthing/syncthing/releases/download/v${PORTVERSION}/
DISTNAME=	${PORTNAME}-source-${DISTVERSIONPREFIX}${PORTVERSION}

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=	yes
NO_LICENSES_DIALOGS=	yes
LICENSE=	MPL20
#LICENSE_FILE=	${WRKSRC}/${PORTNAME}/LICENSE

USES=		cpe go:1.24,modules
BINS=		syncthing stdiscosrv strelaysrv strelaypoolsrv

CONFLICTS_INSTALL=	syncthing1

.for x in ${BINS}
# XigmaNAS
PLIST_FILES=	bin/syncthing
#PLIST_FILES+=   bin/${x} 
.endfor 

PORTDOCS=	*

USERS=		syncthing
GROUPS=		syncthing

# Anonymize
MAKE_ENV+=	BUILD_USER=ports BUILD_HOST=XigmaNAS

STRIP=		# stripping can break go binaries

NO_WRKSUBDIR=   yes
OPTIONS_DEFINE=	DOCS
# XigmaNAS
NO_PKG_REGISTER= yes

.include <bsd.port.pre.mk>

do-build:
	@( cd ${WRKSRC}/${PORTNAME} ; \
		${SETENV} ${MAKE_ENV} ${GO_ENV} GOPROXY=off ${GO_CMD} run build.go -version v${PORTVERSION} -no-upgrade build ; \
		${SETENV} ${MAKE_ENV} ${GO_ENV} GOPROXY=off ${GO_CMD} run build.go -version v${PORTVERSION} -no-upgrade build stdiscosrv; \
		${SETENV} ${MAKE_ENV} ${GO_ENV} GOPROXY=off ${GO_CMD} run build.go -version v${PORTVERSION} -no-upgrade build strelaysrv; \
		${SETENV} ${MAKE_ENV} ${GO_ENV} GOPROXY=off ${GO_CMD} run build.go -version v${PORTVERSION} -no-upgrade build strelaypoolsrv; \
		)

do-install:
.for x in ${BINS}
	${INSTALL_PROGRAM} ${WRKSRC}/${PORTNAME}/${x} \
		${STAGEDIR}${PREFIX}/bin/
.endfor
	${MKDIR} ${STAGEDIR}${DOCSDIR}
.for x in AUTHORS LICENSE README.md
	${INSTALL_DATA} ${WRKSRC}/${PORTNAME}/${x} \
		${STAGEDIR}${DOCSDIR}
.endfor

# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/syncthing.in ${XIGMANAS_ROOTFS}/etc/rc.d/syncthing
# bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/${PORTNAME}/syncthing \
		${XIGMANAS_ROOTFS}${PREFIX}/bin/
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
