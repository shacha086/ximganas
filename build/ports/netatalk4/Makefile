PORTNAME=	netatalk
PORTVERSION=	4.3.2
PORTREVISION=	0
CATEGORIES=	net
MASTER_SITES=	https://github.com/${PORTNAME}/${PORTNAME}/releases/download/netatalk-${PORTVERSION:S/./-/g}/
PKGNAMESUFFIX=	4

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	File server for Mac OS X
WWW=		https://netatalk.io/

# XigmaNAS® contains license files
NO_LICENSES_DIALOGS= yes
LICENSE=	GPLv2

BUILD_DEPENDS=	cmark:textproc/cmark
LIB_DEPENDS=	libgcrypt.so:security/libgcrypt \
		libevent.so:devel/libevent \
		libiniparser.so:devel/iniparser \
		libbstring.so:devel/bstring

USES=		bdb:18 gettext iconv:wchar_t meson perl5 pkgconfig shebangfix ssl tar:xz
USE_LDCONFIG=	yes
USE_RC_SUBR=	netatalk
#INSTALL_TARGET=install-strip
CPE_VENDOR=	netatalk_project

MESON_ARGS=		-Dwith-libgcrypt-path=${LOCALBASE} \
			-Dwith-bdb-path=${LOCALBASE} \
			-Dwith-libiconv-path=${LOCALBASE} \
			-Dwith-dtrace=false \
			-Dwith-init-hooks=false \
			-Dpkg_config_path=${LOCALBASE}/libdata/pkgconfig \
			-Dwith-webmin=false \
			-Dwith-cups=false \
			-Dwith-testsuite=false \
			-Dwith-appletalk=false \
			-Dwith-quota=false \
			-Dwith-zeroconf=false \
			--localstatedir=/var/etc \
			--infodir=${PREFIX}/share/info

OPTIONS_DEFINE= PAM KERBEROS5 LDAP SENDFILE KERBEROS DBUS MYSQL ACL LIBWRAP SPOTLIGHT
OPTIONS_DEFAULT= ACL MDNSRESPONDER PAM
OPTIONS_RADIO=ZEROCONF
OPTIONS_RADIO_ZEROCONF= AVAHI MDNSRESPONDER
ZEROCONF_DESC=		Zeroconf(Bonjour) support
MYSQL_DESC=		Enable MySQL CNID backend support
SENDFILE_DESC=		Enable Sendfile support
ACL_DESC=		Enable ACL support
SPOTLIGHT_DESC=		Enable Spotlight search support
OPTIONS_SUB=	yes
KERBEROS5_MESON_ON=	-Dwith-krbV-uam=true \
			-Dwith-kerberos=true
KERBEROS5_MESON_OFF=	-Dwith-krbV-uam=false \
			-Dwith-kerberos=false
KERBEROS_LIB_DEPENDS=libgpg-error.so:security/libgpg-error
PAM_MESON_ON=		-Dwith-pam-path=${LOCALBASE}
PAM_MESON_OFF=		-Dwith-pam=false
AVAHI_CFLAGS=		-I${LOCALBASE}/include -L${LOCALBASE}/lib
AVAHI_LIB_DEPENDS=		libavahi-client.so:net/avahi-app
AVAHI_SUB_LIST=		ZEROCONF="avahi_daemon"
MDNSRESPONDER_CFLAGS=		-I${LOCALBASE}/include -L${LOCALBASE}/lib
MDNSRESPONDER_LIB_DEPENDS=		libdns_sd.so:net/mDNSResponder
MDNSRESPONDER_SUB_LIST=	ZEROCONF="mdnsd"
LDAP_MESON_ON=		-Dwith-ldap-path=${LOCALBASE}
LDAP_MESON_OFF=		-Dwith-ldap=false
LDAP_CFLAGS=		-I${LOCALBASE}/include -L${LOCALBASE}/lib
LDAP_USES=		ldap
SENDFILE_MESON_OFF=	-Dwith-sendfile=false
DBUS_USES=		gnome python
DBUS_USE=		GNOME+=glib20
DBUS_LIB_DEPENDS=	libdbus-glib-1.so:devel/dbus-glib \
			libdbus-1.so:devel/dbus
DBUS_RUN_DEPENDS=	${PYTHON_SITELIBDIR}/dbus/_dbus.py:devel/py-dbus@${PY_FLAVOR}
DBUS_MESON_ON=		-Dwith-dbus-daemon-path=${LOCALBASE}/bin/dbus-daemon \
			-Dwith-dbus-sysconf-path=${LOCALBASE}/etc/dbus-1/system.d
DBUS_MESON_OFF=		-Dwith-afpstats=false
MYSQL_USES=		mysql
MYSQL_MESON_ON=-Dwith-cnid-default-backend=mysql
MYSQL_MESON_OFF=-Dwith-cnid-default-backend=dbd
ACL_LIB_DEPENDS=	libsunacl.so:sysutils/libsunacl
ACL_MESON_OFF=		-Dwith-acls=false
LIBWRAP_MESON_OFF=-Dwith-tcp-wrappers=false
SPOTLIGHT_LIB_DEPENDS=	libtinysparql-3.0.so:devel/tinysparql
SPOTLIGHT_MESON_OFF=	-Dwith-spotlight=false

SHEBANG_GLOB=	afpstats

CONFLICTS=	netatalk3

CONFLICTS=	jday yudit # bin/dbd man/man1/uniconv.1.gz

.include <bsd.port.pre.mk>

.if ${PORT_OPTIONS:MAVAHI}=="" && ${PORT_OPTIONS:MMDNSRESPONDER}==""
SUB_LIST+=	ZEROCONF=""
MESON_ARGS+=-Dwith-zeroconf=false
.endif

post-patch:
	@${REINPLACE_CMD} -e 's|%%DB_NAME%%|${BDB_INCLUDE_DIR:T}|' \
		${WRKSRC}/meson.build

post-install:
	${INSTALL_SCRIPT} ${BUILD_WRKSRC}/contrib/bin_utils/macusers \
		${STAGEDIR}${PREFIX}/bin/macusers

# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_SCRIPT} -v ${FILESDIR}/netatalk.in ${XIGMANAS_ROOTFS}/etc/rc.d/netatalk
# bin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/bin/dbd ${XIGMANAS_ROOTFS}${PREFIX}/bin
# sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/afpd ${XIGMANAS_ROOTFS}${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_dbd ${XIGMANAS_ROOTFS}${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/cnid_metad ${XIGMANAS_ROOTFS}${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/sbin/netatalk ${XIGMANAS_ROOTFS}${PREFIX}/sbin
# lib
	@${INSTALL_LIB} -v ${STAGEDIR}${PREFIX}/lib/libatalk.so.* ${XIGMANAS_ROOTFS}${PREFIX}/lib
	@${LN} -f -v -s libatalk.so.19 ${XIGMANAS_ROOTFS}${PREFIX}/lib/libatalk.so
	@${LN} -f -v -s /usr/local/lib/mysql/libmariadb.so.3 ${XIGMANAS_ROOTFS}${PREFIX}/lib/libmariadb.so.3

# etc
	@${INSTALL_DATA} -v ${STAGEDIR}${PREFIX}/etc/extmap.conf.sample ${XIGMANAS_ROOTFS}${PREFIX}/etc/extmap.conf
# lib
	@${MKDIR} -v ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_dhx_pam.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_dhx_passwd.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_dhx2_pam.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_dhx2_passwd.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
#	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_gss.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_guest.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_pam.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_passwd.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
#	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_pgp.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@${INSTALL_PROGRAM} -v ${STAGEDIR}${PREFIX}/libexec/netatalk-uams/uams_randnum.so ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams
	@cd ${XIGMANAS_ROOTFS}${PREFIX}/libexec/netatalk-uams && ${LN} -fsv uams_passwd.so uams_clrtxt.so && ${LN} -fsv uams_dhx_passwd.so uams_dhx.so && ${LN} -fsv uams_dhx2_passwd.so uams_dhx2.so
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
