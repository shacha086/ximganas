# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# XigmaNAS is a registered trademark of Michael Zoon.
# All rights reserved.
#
# Bug 218579 - bge(4): Wake on Lan (WoL) does not work.
# Add Latest version of patch for 14.0R.
#

PATCHFILE1=patch-if_bge.c.diff
PATCHFILE2=patch-if_bgereg.h.diff

install:
	(cd /usr/src; patch < ${.CURDIR}/files/${PATCHFILE1})
	(cd /usr/src; patch < ${.CURDIR}/files/${PATCHFILE2})
