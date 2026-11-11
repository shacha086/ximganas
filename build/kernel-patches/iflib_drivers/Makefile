# Part of XigmaNAS® (https://www.xigmanas.com).
# Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
# XigmaNAS is a registered trademark of Michael Zoon.
# All rights reserved.
#
# Fixes for 14.3 release.
#
# * iflib: report output drops and handle ENOBUFS properly
#
# - Fix an mbuf leak with iflib.simple_tx=1 when we run out of tx descs
#   in iflib_encap().  It seems odd to free the mbuf in iflib_encap(),
#   but that routine consumes mbufs for other reasons, and it seemed
#   safest to free there rather than have the simple tx routine parse
#   return values to determine what needed to be freed.
#
# - Increment counters for output drops when ENOBUFS is encountered
#   and output errors when other transmit errors are encountered for both
#   the simple and normal tx routines.
#
# - Performed driver changes so that iflib drivers now add the generic
#   output drop and output error counters to their private counters in their
#   ifdi_get_counter routines.
#
# - Add fix igc driver for igc_neweitr()
#

PATCHFILE1=patch-sys__dev__axgbe_if_axgbe_pci.c
PATCHFILE2=patch-sys__dev__e1000_e1000_phy.c
PATCHFILE3=patch-sys__dev__e1000_if_em.c
PATCHFILE4=patch-sys__dev__enetc_if_enetc.c
PATCHFILE5=patch-sys__dev__ice_ice_lib.c
PATCHFILE6=patch-sys__dev__igc_if_igc.c
PATCHFILE7=patch-sys__dev__ixgbe_if_ix.c
PATCHFILE8=patch-sys__dev__ixl_if_ixl.c
PATCHFILE9=patch-sys__net__iflib.c

install:
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE1})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE2})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE3})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE4})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE5})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE6})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE7})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE8})
	(cd /usr/src/sys; patch < ${.CURDIR}/files/${PATCHFILE9})
