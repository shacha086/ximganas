--- dev/ice/ice_lib.c.orig	2025-10-17 07:47:25.354465000 +0200
+++ dev/ice/ice_lib.c	2025-10-17 10:08:47.000000000 +0200
@@ -7790,7 +7790,8 @@
 	case IFCOUNTER_OPACKETS:
 		return (es->tx_unicast + es->tx_multicast + es->tx_broadcast);
 	case IFCOUNTER_OERRORS:
-		return (es->tx_errors);
+		return (if_get_counter_default(vsi->sc->ifp, counter) +
+		    es->tx_errors);
 	case IFCOUNTER_COLLISIONS:
 		return (0);
 	case IFCOUNTER_IBYTES:
@@ -7804,7 +7805,8 @@
 	case IFCOUNTER_IQDROPS:
 		return (es->rx_discards);
 	case IFCOUNTER_OQDROPS:
-		return (hs->tx_dropped_link_down);
+		return (if_get_counter_default(vsi->sc->ifp, counter) +
+		    hs->tx_dropped_link_down);
 	case IFCOUNTER_NOPROTO:
 		return (es->rx_unknown_protocol);
 	default:
