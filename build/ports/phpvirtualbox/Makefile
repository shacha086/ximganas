PORTNAME=	phpvirtualbox
DISTVERSION=	7.2-2
PORTREVISION=	0
CATEGORIES=	www
SUFFIX=		-72
PKGNAMESUFFIX=	${SUFFIX}${PHP_PKGNAMESUFFIX}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	AJAX Web Interface for VirtualBox
WWW=		https://github.com/phpvirtualbox/phpvirtualbox/

# XigmaNAS® contains license files
#LICENSE=	GPLv3

USES=		dos2unix php
# XigmaNAS

NO_ARCH=	yes
NO_BUILD=	yes
USE_GITHUB=	yes
# USE_PHP=	session simplexml soap xml

ETCDIR=		${WWWDIR}
WWWDIR=		${PREFIX}/www/${PORTNAME}
NO_ARCH=	yes
NO_BUILD=	yes
PORTSCOUT=	limit:^7\.2\.

DOS2UNIX_REGEX=	.*\.(php|txt|js|css|html)
ETCDIR=		${WWWDIR}

post-patch:
	@${REINPLACE_CMD} -e 's#/usr/share/phpvirtualbox#${WWWDIR}#g' \
		${WRKSRC}/phpvirtualbox.conf
do-install:
	${MKDIR} ${STAGEDIR}${WWWDIR}
	(cd ${WRKSRC}/ && ${COPYTREE_SHARE} "*" ${STAGEDIR}${WWWDIR} \
	    "-not -name *.orig -not -name config.php-example")
	${INSTALL_DATA} ${WRKSRC}/config.php-example \
	    ${STAGEDIR}${WWWDIR}/config.php.sample
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	${RM} -f ${STAGEDIR}${WWWDIR}/*.txt
	${RM} -f ${STAGEDIR}${WWWDIR}/*.bak
	${CP} -Rpv ${STAGEDIR}${WWWDIR} ${XIGMANAS_ROOTFS}${PREFIX}/www
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
