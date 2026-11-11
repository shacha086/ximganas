PORTNAME=		xigmanas-mariadb
PORTVERSION=	1.0
PORTREVISION=	0
CATEGORIES=	databases

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Multithreaded SQL database
WWW=		https://mariadb.org/

DISTFILES?=
NO_BUILD?=	yes
NO_PKG_REGISTER?= yes
DEPENDS_TARGET=	clean build stage

DEP_STAGEDIR=	${PORTSDIR}/databases/mariadb118-server/work/stage
CLIENT_STAGEDIR= ${PORTSDIR}/databases/mariadb118-client/work/stage

BUILD_DEPENDS=	${NONEXISTENT}:databases/mariadb118-server
BUILD_DEPENDS+=	${NONEXISTENT}:databases/mariadb118-client

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
# rc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mysqldb.in ${XIGMANAS_ROOTFS}/etc/rc.d/mysqldb
	@${INSTALL_SCRIPT} -v ${FILESDIR}/mysql-server.in ${XIGMANAS_ROOTFS}${PREFIX}/etc/rc.d/mysql-server
# bin
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/bin ${XIGMANAS_ROOTFS}${PREFIX}
	@${CP} -Rpv ${CLIENT_STAGEDIR}${PREFIX}/bin ${XIGMANAS_ROOTFS}${PREFIX}
# share
	@${RM} -rf  ${XIGMANAS_ROOTFS}${PREFIX}/share/mysql
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/share/mysql ${XIGMANAS_ROOTFS}${PREFIX}/share
# lib
	@${RM} -rf  ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql
	@${CP} -Rpv ${DEP_STAGEDIR}${PREFIX}/lib/mysql ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${CP} -Rpv ${CLIENT_STAGEDIR}${PREFIX}/lib/mysql ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${INSTALL_PROGRAM} -v ${DEP_STAGEDIR}${PREFIX}/libexec/mariadbd ${XIGMANAS_ROOTFS}${PREFIX}/libexec
	@${LN} -fsv /usr/local/libexec/mariadbd ${XIGMANAS_ROOTFS}${PREFIX}/libexec/mysqld

post-install:
	${RM} ${XIGMANAS_ROOTFS}${PREFIX}/bin/wsrep*
	${RM} ${XIGMANAS_ROOTFS}${PREFIX}/share/aclocal/mysql.m4
	${RM} -r ${XIGMANAS_ROOTFS}${PREFIX}/share/pkgconfig
	${RM} -r ${XIGMANAS_ROOTFS}${PREFIX}/include/mysql/private
.	for file in mysql mysqladmin mysqlbinlog mysqlcheck mysqldump mysqlimport mysqlshow mysqlslap
		${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/bin/${file}
.	endfor
	${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/libmysqlclient.so
.	for file in dialog.so mysql_clear_password.so
		${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/plugin/${file}
.	endfor
	# Fix https://mariadb.atlassian.net/browse/MDEV-9388
	@${REINPLACE_CMD} 's/-l-pthread/-lpthread/' ${XIGMANAS_ROOTFS}${PREFIX}/bin/mysql_config

	${STRIP_CMD} ${XIGMANAS_ROOTFS}${PREFIX}/lib/mysql/plugin/auth_gssapi_client.so
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
