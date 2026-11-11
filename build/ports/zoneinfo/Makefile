PORTNAME=		zoneinfo
PORTREVISION=	0
DISTVERSION=	2025b
CATEGORIES=	misc
MASTER_SITES=   https://data.iana.org/time-zones/releases/ \
		ftp://ftp.iana.org/tz/releases/
DISTNAME=	tzdata${DISTVERSION}

MAINTAINER=	zoon01@xigmanas.com
COMMENT=	Updated timezone definitions
WWW=		https://www.iana.org/time-zones

# XigmaNAS® contains license files
NO_LICENSES_INSTALL=	yes
NO_LICENSES_DIALOGS=	yes
LICENSE=	PD

WRKSRC=	${WRKDIR}
PREFIX=	/usr
NO_ARCH=	yes

OPTIONS_DEFINE=	BACKWARD
OPTIONS_DEFAULT= BACKWARD
OPTIONS_SUB=	yes

BACKWARD_DESC=	Include backwards compatible zones
BACKWARD_VARS=	tzfiles+=backward

TZFILES=	africa antarctica asia australasia etcetera europe \
		factory northamerica southamerica
POSIXRULES=	America/New_York

post-patch:
	cd ${WRKSRC}; \
	badc=$$(${AWK} '/^[^\#]/ { t[$$1]=1; }; \
			END { for (i=0; i<676; ++i) { \
				c = sprintf("%c%c", 65+int(i/26), 65+(i%26)); \
				if (!(c in t)) print c; \
			      } \
			}' \
			/usr/share/misc/iso3166 | \
			${SED} -n -e '1h; 1!H; $${g;s/\n/|/g;p;}'); \
	for fn in zone.tab zone1970.tab; do \
		${MV} $${fn} $${fn}.bak; \
		${AWK} -v FS="\t" -v OFS="\t" -v re="(^|,)($${badc})" \
			'/^[^#]/ && $$1 ~ re { \
				print "#" $$0; \
				gsub(re,"",$$1); \
				sub(/^,/,"",$$1); \
			 }; \
			 $$1 != "" { print; }' <$${fn}.bak >$${fn}; \
	done

do-build:
	umask 022; \
	cd ${WRKSRC}; \
	zic -d ${WRKSRC}/zoneinfo -p ${POSIXRULES} -m ${NOBINMODE} \
		-y ${.OBJDIR}/yearistype ${TZFILES}

do-install:
# Compress zoneinfo data, exclude some useless files.
	@${ECHO_CMD} "Factory" > ${WRKSRC}/zoneinfo.exlude
	@${ECHO_CMD} "posixrules" >> ${WRKSRC}/zoneinfo.exlude
	@${ECHO_CMD} "zone.tab" >> ${WRKSRC}/zoneinfo.exlude

# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${MKDIR} -p ${XIGMANAS_ROOTFS}/usr/share
#	Copy into temporary folder without hardlinks
	cp -RLpf ${WRKSRC}/zoneinfo ${WRKSRC}/zoneinfo.tmp
	${TAR} -c -v -f - -X ${WRKSRC}/zoneinfo.exlude -C ${WRKSRC}/zoneinfo.tmp/ . | ${XZ_CMD} -cv > ${XIGMANAS_ROOTFS}/usr/share/zoneinfo.txz
	rm -rf ${WRKSRC}/zoneinfo.tmp
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
