PORTNAME=		lcdproc
PORTVERSION=	${LCDPROC_COMMIT_DATE}
PORTREVISION=	0
# DISTVERSIONPREFIX= lcdproc-
CATEGORIES=	sysutils
COMMENT=	Client/Server suite for LCD devices
WWW=		https://www.lcdproc.org/
MAINTAINER=	zoon01@xigmanas.com

PKGNAMESUFFIX=	-devel

CONFLICTS=	lcdproc-[0-9]*

USE_GITHUB=	yes
GH_ACCOUNT=	lcdproc
GH_TAGNAME=	0e2ce9b9c
LCDPROC_COMMIT_DATE= 2021.12.20

GNU_CONFIGURE=	yes
USES=           autoreconf pkgconfig

# Options
OPTIONS_DEFINE= USB FTDI FREETYPE SERDISP

LIB_DEPENDS+=	libftdi1.so:${PORTSDIR}/devel/libftdi1

CONFIGURE_ARGS=	--enable-drivers="${LCDPROC_DRIVERS}" \
				--disable-dependency-tracking \
				--with-libusb \
				--disable-libhid \
				--disable-doxygen

OPTIONS_DEFINE=  USB FREETYPE
USB_DESC=	   USB support
FTDI_DESC=	   Build drivers using libftdi (requires USB)
HID_DESC=	   Build drivers using libhid (requires USB)
SERDISP_DESC=	   Build drivers using serdisplib


OPTIONS_DEFAULT= USB FTDI FREETYPE SERDISP
NO_OPTIONS_SORT= yes

FREETYPE_LIB_DEPENDS=	libfreetype.so:${PORTSDIR}/print/freetype2
FREETYPE_CONFIGURE_ENABLE=	freetype

PNG_LIB_DEPENDS=	libpng.so:${PORTSDIR}/graphics/png
PNG_CONFIGURE_ENABLE=	libpng

SERDISP_LIB_DEPENDS=	libserdisp.so:${PORTSDIR}/devel/serdisplib
SERDISP_CFLAGS=		-I${LOCALBASE}/include
SERDISP_LDFLAGS= 	-L${LOCALBASE}/lib
SERDISP_CPPFLAGS=	-I${LOCALBASE}/include

LCDPROC_DRIVERS=	bayrad \
				CFontz \
				CFontzPacket \
				curses \
				CwLnx \
				ea65 \
				EyeboxOne \
				futaba \
				glcd \
				glk \
				hd44780 \
				icp_a106 \
				lb216 \
				lcdm001 \
				lcterm \
				MD8800 \
				ms6931 \
				mtc_s16209x \
				MtxOrb \
				NoritakeVFD \
				pyramid \
				SureElec \
				sdeclcd \
				sed1330 \
				sed1520 \
				serialPOS \
				sli \
				stv5730 \
				t6963 \
				text \
				tyan \
				vlsys_m428
LCDPROC_DRIVERS+=	IOWarrior \
				picolcd \
				shuttleVFD
LCDPROC_DRIVERS+=	i2500vfd \
				lis \
				ula200
LCDPROC_DRIVERS+=	mdm166a

.include <bsd.port.pre.mk>
.if (${ARCH} == "amd64" || ${ARCH} == "i386")
LCDPROC_DRIVERS+=	serialVFD
.endif

.include <bsd.port.options.mk>

.if ${PORT_OPTIONS:MUSB}
CONFIGURE_ARGS+=	--with-libusb=/usr
PLIST_SUB+=		USB=""
LCDPROC_DRIVERS+=IOWarrior \
			picolcd \
			shuttleVFD

. if ${PORT_OPTIONS:MFTDI}
LIB_DEPENDS+=		libftdi1.so:${PORTSDIR}/devel/libftdi1
CONFIGURE_ARGS+=	--enable-libftdi
PLIST_SUB+=		FTDI=""
LCDPROC_DRIVERS+=i2500vfd \
			lis \
			ula200
. else
CONFIGURE_ARGS+=	--disable-libftdi
PLIST_SUB+=		FTDI="@comment "
. endif

.else
CONFIGURE_ARGS+=	--without-libusb --disable-libftdi --disable-libhid
PLIST_SUB+=		USB="@comment " FTDI="@comment " HID="@comment "
.endif

do-install:
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/LCDd ${XIGMANAS_ROOTFS}${PREFIX}/sbin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdexec/lcdexec ${XIGMANAS_ROOTFS}${PREFIX}/bin
	@${INSTALL_PROGRAM} -v ${WRKSRC}/clients/lcdproc/lcdproc ${XIGMANAS_ROOTFS}${PREFIX}/bin
	@${MKDIR} -v ${XIGMANAS_ROOTFS}/${PREFIX}/lib/lcdproc
	@${INSTALL_PROGRAM} -v ${WRKSRC}/server/drivers/*.so ${XIGMANAS_ROOTFS}${PREFIX}/lib/lcdproc
	@${MKDIR} -v ${XIGMANAS_ROOTFS}${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/LCDd.conf ${XIGMANAS_ROOTFS}${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdexec/lcdexec.conf ${XIGMANAS_ROOTFS}${PREFIX}/share/lcdproc
	@${INSTALL_DATA} -v ${WRKSRC}/clients/lcdproc/lcdproc.conf ${XIGMANAS_ROOTFS}${PREFIX}/share/lcdproc
	@${INSTALL_SCRIPT} -v ${FILESDIR}/LCDd.in ${XIGMANAS_ROOTFS}/etc/rc.d/LCDd
	@${INSTALL_SCRIPT} -v ${FILESDIR}/lcdproc.in ${XIGMANAS_ROOTFS}/etc/rc.d/lcdproc
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.post.mk>
