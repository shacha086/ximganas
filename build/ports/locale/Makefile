PORTNAME=		locale
PORTVERSION=	1.0
PORTREVISION=	2
CATEGORIES=	lang
COMMENT=	Language packages for XigmaNAS®
MAINTAINER=	zoon01@xigmanas.com

DISTFILES?=
USES+=  gettext

.include <../xigmanas.mk>

do-build:
# Translate *.po files to *.mo.
	@for i in $$(ls ${XIGMANAS_SVNDIR}/locale/*.po); do \
		filename=$$(basename $$i); \
		language=$${filename%*.po}; \
		language=$${language#*-}; \
		filename=$$(echo ${XIGMANAS_PRODUCTNAME} | tr '[A-Z]' '[a-z]'); \
		${MKDIR} -v ${WRKSRC}/$$language/LC_MESSAGES; \
		msgfmt -v --output-file="${WRKSRC}/$$language/LC_MESSAGES/$$filename.mo" $$i; \
	done

do-install:
# We only install maintained translations for WebGUI
# XigmaNAS® install
	@echo "Start install to ${XIGMANAS_ROOTFS}"
	@${CP} -vp -r ${WRKSRC}/af ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/ca ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/cs ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/de ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/el ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/es ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/fr ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/hu ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/it ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/ja ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/ko ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/lo ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/nl ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/pt_BR ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/ro ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/ru ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/sv ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/uk ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/zh_CN ${XIGMANAS_ROOTFS}/usr/local/share/locale
	@${CP} -vp -r ${WRKSRC}/zh_TW ${XIGMANAS_ROOTFS}/usr/local/share/locale
# XigmaNAS® end install
	@echo "End install to ${XIGMANAS_ROOTFS}"

.include <bsd.port.mk>
