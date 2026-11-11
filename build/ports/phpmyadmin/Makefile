PORTNAME=	phpMyAdmin
DISTVERSION=	5.2.3
PORTREVISION=	0
CATEGORIES=	databases www
MASTER_SITES=	https://files.phpmyadmin.net/${PORTNAME}/${DISTVERSION}/
DISTNAME=	${PORTNAME}-${DISTVERSION}-all-languages

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Set of PHP-scripts to manage MySQL over the web

USES=		cpe tar:xz
CPE_PRODUCT=	${PORTNAME:tl}
ETCDIR=	${WWWDIR}

#DISABLE_VULNERABILITIES=yes
NO_ARCH=	yes
NO_BUILD=	yes

CFG_FILE=	config.inc.php

SUB_LIST+=	PKGNAME=${PKGNAME}	        \
		CFG_FILE=${WWWDIR}/${CFG_FILE}

.SILENT:

do-install:
	cd ${WRKSRC} ; \
	for src in $$( ${FIND} . ! -name .cvsignore ! -name '*.bak' -not -name *.orig) ; do \
	    dst=${STAGEDIR}${WWWDIR}$${src#.} ; \
	    if ${TEST} -d "$$src" ; then \
		${MKDIR} "$$dst" ; \
	    else \
		${INSTALL_DATA} "$$src" "$$dst" ; \
	    fi \
	done

# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${RM} -rf ${STAGEDIR}${WWWDIR}$${dst}/test
	@${RM} -rf ${STAGEDIR}${WWWDIR}$${dst}/setup
	@${CP} -Rpv ${STAGEDIR}${WWWDIR} ${XIGMANAS_ROOTFS}${PREFIX}/www
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
