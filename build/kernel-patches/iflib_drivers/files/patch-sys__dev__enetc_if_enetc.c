--- dev/enetc/if_enetc.c.orig	2025-10-17 07:47:25.255022000 +0200
+++ dev/enetc/if_enetc.c	2025-10-17 10:06:09.000000000 +0200
@@ -1345,7 +1345,8 @@
 	case IFCOUNTER_IERRORS:
 		return (ENETC_PORT_RD8(sc, ENETC_PM0_RERR));
 	case IFCOUNTER_OERRORS:
-		return (ENETC_PORT_RD8(sc, ENETC_PM0_TERR));
+		return (if_get_counter_default(ifp, cnt) +
+		    ENETC_PORT_RD8(sc, ENETC_PM0_TERR));
 	default:
 		return (if_get_counter_default(ifp, cnt));
 	}
