--- dev/ixl/if_ixl.c.orig	2025-10-17 07:47:25.447507000 +0200
+++ dev/ixl/if_ixl.c	2025-10-17 10:16:59.000000000 +0200
@@ -1776,7 +1776,7 @@
 	case IFCOUNTER_OPACKETS:
 		return (vsi->opackets);
 	case IFCOUNTER_OERRORS:
-		return (vsi->oerrors);
+		return (if_get_counter_default(ifp, cnt) + vsi->oerrors);
 	case IFCOUNTER_COLLISIONS:
 		/* Collisions are by standard impossible in 40G/10G Ethernet */
 		return (0);
@@ -1791,7 +1791,7 @@
 	case IFCOUNTER_IQDROPS:
 		return (vsi->iqdrops);
 	case IFCOUNTER_OQDROPS:
-		return (vsi->oqdrops);
+		return (if_get_counter_default(ifp, cnt) + vsi->oqdrops);
 	case IFCOUNTER_NOPROTO:
 		return (vsi->noproto);
 	default:
