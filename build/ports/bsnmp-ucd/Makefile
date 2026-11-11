PORTNAME=		bsnmp-ucd
PORTVERSION=	0.4.5
PORTREVISION=	1
CATEGORIES=	net-mgmt
COMMENT=	A bsnmpd module that implements parts of UCD-SNMP-MIB
MAINTAINER=	zoon01@xigmanas.com

USE_GITHUB=	yes
GH_ACCOUNT=	trociny

USE_MAKE=		yes
USES=           uidfix
MAKE_ENV+=      NO_OBJ=yes
MAKE_ARGS=	MANDIR=${PREFIX}/share/man/man

do-install:
# XigmaNAS® Install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/snmp_ucd.so.* ${XIGMANAS_ROOTFS}/usr/lib
	${LN} -sfv /usr/lib/snmp_ucd.so.1 ${XIGMANAS_ROOTFS}/usr/lib/snmp_ucd.so

	@${INSTALL_DATA} ${WRKSRC}/UCD-SNMP-MIB.txt ${XIGMANAS_ROOTFS}/usr/share/snmp/mibs
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>