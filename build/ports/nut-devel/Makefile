PORTNAME=		nut
PORTVERSION=	${NUT_COMMIT_DATE}
PORTREVISION=	0
CATEGORIES=	sysutils
COMMENT=	Network UPS tools
WWW=		https://www.networkupstools.org/
MAINTAINER=	zoon01@xigmanas.com

PKGNAMESUFFIX=	-devel

CONFLICTS=	nut-[0-9]*

USE_GITHUB=	yes
GH_ACCOUNT=	networkupstools
GH_TAGNAME=	41daedfab
NUT_COMMIT_DATE=	2025.11.17

GNU_CONFIGURE=	yes
USES=		autoreconf:build gmake libtool pkgconfig python:build
USE_LDCONFIG=	yes

GNU_CONFIGURE_MANPREFIX=${PREFIX}/share


NUT_USER=		root
NUT_GROUP=		wheel
NUT_PIDDIR=		/var/run
NUT_STATEDIR=		/var/db/nut
NUT_SYSCONFDIR=		/var/etc
NUT_DATADIR=		/usr/local/etc/${PORTNAME}
NUT_DRVDIR=		/usr/local/libexec/${PORTNAME}

MODBUS_CONFIGURE_WITH=	modbus

CONFIGURE_ARGS+=	--sysconfdir=${NUT_SYSCONFDIR} \
		--program-transform-name="" \
		--localstatedir=${NUT_STATEDIR} \
		--datadir=${NUT_DATADIR} \
		--with-devd-dir=${PREFIX}/etc/devd \
		--with-drvpath=${NUT_DRVDIR} \
		--with-statepath=${NUT_STATEDIR} \
		--with-altpidpath=${NUT_PIDDIR} \
		--with-pidpath=${NUT_PIDDIR} \
		--without-pkgconfig-dir \
		--without-augeas-lenses-dir \
		--enable-extapi-enphase=no \
		--with-serial \
		--with-usb=auto \
		--with-snmp \
		--with-neon \
		--with-powerman \
		--without-pynut \
		--without-cgi \
		--without-dev \
		--without-avahi \
		--with-user=${NUT_USER} \
		--with-group=${NUT_GROUP} \
		--with-python=${PYTHON_CMD} \
		--without-python2 \
		--with-python3=${PYTHON_CMD} \
		--with-wrap

.include <bsd.port.pre.mk>

CFLAGS+=	-I${LOCALBASE}/include -L${LOCALBASE}/lib
# XXX Remove this when copying this port to sysutils/nut. The GA version
# XXX of nut ships with preformatted files and therefore does not need
# XXX asciidoc.
BUILD_DEPENDS=  asciidoc:textproc/asciidoc
LIB_DEPENDS+=	libltdl.so:devel/libltdl
LIB_DEPENDS+=	libnetsnmp.so:net-mgmt/net-snmp
LIB_DEPENDS+=   libneon.so:www/neon
LIB_DEPENDS+=	libpowerman.so:sysutils/powerman
LIB_DEPENDS+=	libmodbus.so:comms/libmodbus
LIB_DEPENDS+=	libfreeipmi.so:sysutils/freeipmi
CONFIGURE_ARGS+=--with-ipmi \
		--with-freeipmi


post-patch:
	@${REINPLACE_CMD} -i '' -e 's|python|${PYTHON_CMD}|' ${WRKSRC}/autogen.sh
	@${FIND} ${WRKSRC} -type f | ${XARGS} ${REINPLACE_CMD} -i '' -e 's|/usr/bin/env python|${PYTHON_CMD}|'

pre-configure:
	cd ${WRKSRC} && ./autogen.sh

pre-install:
	@${REINPLACE_CMD} -e 's/device-name\*/cdev/g' ${WRKSRC}/scripts/devd/nut-usb.conf

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${MKDIR} -pv ${XIGMANAS_ROOTFS}${PREFIX}/etc/devd
	@${INSTALL_SCRIPT} -v ${WRKSRC}/scripts/devd/nut-usb.conf ${XIGMANAS_ROOTFS}${PREFIX}/etc/devd/nut-usb.conf

	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/cmdvartab ${XIGMANAS_ROOTFS}${NUT_DATADIR}
	@${INSTALL_DATA} -v ${WRKSRC}/data/driver.list ${XIGMANAS_ROOTFS}${NUT_DATADIR}

	@${MKDIR} -pv ${XIGMANAS_ROOTFS}/${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/adelsystem_cbi ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/al175 ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcsmart-old ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/apcupsd-ups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bcmxcp_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkin ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/belkinunv ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfcom ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestfortress ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestuferrups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bestups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/bicker_ser ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_ser ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/blazer_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/clone-outlet ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/.libs/dummy-ups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/etapro ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/everups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/gamatronic ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/generic_modbus ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/genericups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/huawei-ups2000 ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/isbmex ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/ivtscd ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert-esp2 ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/liebert-gxe ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/masterguard ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/metasys ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-shut ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/mge-utalk ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/microdowell ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/microsol-apc ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/netxml-ups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_atcl_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_qx ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_siemens-sitop ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nut-ipmipsu ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/nutdrv_hashx ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/oneac ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/optiups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/phoenixcontact_modbus ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powercom ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerman-pdu ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powervar_cx_ser ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powerpanel ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/powervar_cx_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/rhino ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/richcomm_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_ser ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/riello_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/safenet ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/skel ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/snmp-ups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/socomec_jbus ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/sms_ser ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/solis ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplite_usb ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/tripplitesu ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upscode2 ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/usbhid-ups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/victronups ${XIGMANAS_ROOTFS}${NUT_DRVDIR}

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/libupsclient.so.7 ${XIGMANAS_ROOTFS}/usr/local/lib
	@${LN} -f -v -s /usr/local/lib/libupsclient.so.7 ${XIGMANAS_ROOTFS}/usr/local/lib/libupsclient.so
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/tools/nut-scanner/.libs/libnutscan.so.4 ${XIGMANAS_ROOTFS}/usr/local/lib
#	@${LN} -f -v -s /usr/local/lib/libnutscan.so.4 ${XIGMANAS_ROOTFS}/usr/local/lib/libupsclient.so

	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsc ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upscmd ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upslog ${XIGMANAS_ROOTFS}/usr/local/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsmon ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/.libs/upsrw ${XIGMANAS_ROOTFS}/usr/local/bin
#	@${INSTALL_PROGRAM} -v ${WRKSRC}/tools/nut-scanner/.libs/nut-scanner ${XIGMANAS_ROOTFS}/usr/local/bin

	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/upsd ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/drivers/upsdrvctl ${XIGMANAS_ROOTFS}/usr/local/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/upssched ${XIGMANAS_ROOTFS}/usr/local/sbin

	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upslog.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut_upslog
	@${INSTALL_SCRIPT} -v ${FILESDIR}/nut_upsmon.in ${XIGMANAS_ROOTFS}/etc/rc.d/nut_upsmon
	@${INSTALL_SCRIPT} -v ${FILESDIR}/upssched-cmd.in ${XIGMANAS_ROOTFS}/usr/local/bin/upssched-cmd
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
