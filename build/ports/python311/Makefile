PORTNAME=		python311
PORTVERSION=	${PYTHON_MAJOR_VER}
PORTREVISION=	0
CATEGORIES=	lang python

COMMENT=	Interpreted object-oriented programming language
MAINTAINER=	zoon01@xigmanas.com

USES=		python:run

DISTFILES=
NO_BUILD=	yes
NO_PKG_REGISTER= yes
#ALWAYS_BUILD_DEPENDS= yes
DEPENDS_TARGET=	clean build stage
DISABLE_VULNERABILITIES=yes

BUILD_DEPENDS=	${NONEXISTENT}:lang/python311
DEP_STAGEDIR=	${PORTSDIR}/lang/python311/work/stage

# To test python, run samba-tool in XigmaNAS

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# bin
.for file in python3.11
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/bin/${file} ${XIGMANAS_ROOTFS}/usr/local/bin
.endfor
# lib
	@${MKDIR} -p ${XIGMANAS_ROOTFS}${PREFIX}/lib/python3.11
	${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/python3.11/$${file} ${XIGMANAS_ROOTFS}${PREFIX}/lib/python3.11; \
	${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/libpython3.11.so* ${XIGMANAS_ROOTFS}/usr/local/lib
	find ${XIGMANAS_ROOTFS}/usr/local/lib/libpython3.11.so.1.0-gdb.py -delete
	find ${XIGMANAS_ROOTFS}/usr/local/lib/python3.11/site-packages/README.txt -delete
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
