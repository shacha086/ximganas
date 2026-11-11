--- dev/e1000/if_em.c.orig	2025-10-17 07:47:25.252836000 +0200
+++ dev/e1000/if_em.c	2025-10-17 12:36:15.000000000 +0200
@@ -1644,14 +1644,16 @@
     struct tx_ring *txr, struct rx_ring *rxr)
 {
 	struct e1000_hw *hw = &sc->hw;
+	unsigned long bytes, bytes_per_packet, packets;
+	unsigned long rxbytes, rxpackets, txbytes, txpackets;
 	u32 newitr;
-	u32 bytes;
-	u32 bytes_packets;
-	u32 packets;
 	u8 nextlatency;
 
+	rxbytes = atomic_load_long(&rxr->rx_bytes);
+	txbytes = atomic_load_long(&txr->tx_bytes);
+
 	/* Idle, do nothing */
-	if ((txr->tx_bytes == 0) && (rxr->rx_bytes == 0))
+	if (txbytes == 0 && rxbytes == 0)
 		return;
 
 	newitr = 0;
@@ -1671,17 +1673,20 @@
 			goto em_set_next_itr;
 		}
 
+		bytes = bytes_per_packet = 0;
 		/* Get largest values from the associated tx and rx ring */
-		if (txr->tx_bytes && txr->tx_packets) {
-			bytes = txr->tx_bytes;
-			bytes_packets = txr->tx_bytes/txr->tx_packets;
-			packets = txr->tx_packets;
+		txpackets = atomic_load_long(&txr->tx_packets);
+		if (txpackets != 0) {
+			bytes = txbytes;
+			bytes_per_packet = txbytes / txpackets;
+			packets = txpackets;
 		}
-		if (rxr->rx_bytes && rxr->rx_packets) {
-			bytes = max(bytes, rxr->rx_bytes);
-			bytes_packets =
-			    max(bytes_packets, rxr->rx_bytes/rxr->rx_packets);
-			packets = max(packets, rxr->rx_packets);
+		rxpackets = atomic_load_long(&rxr->rx_packets);
+		if (rxpackets != 0) {
+			bytes = lmax(bytes, rxbytes);
+			bytes_per_packet =
+			    lmax(bytes_per_packet, rxbytes / rxpackets);
+			packets = lmax(packets, rxpackets);
 		}
 
 		/* Latency state machine */
@@ -1691,7 +1696,7 @@
 			break;
 		case itr_latency_lowest: /* 70k ints/s */
 			/* TSO and jumbo frames */
-			if (bytes_packets > 8000)
+			if (bytes_per_packet > 8000)
 				nextlatency = itr_latency_bulk;
 			else if ((packets < 5) && (bytes > 512))
 				nextlatency = itr_latency_low;
@@ -1699,14 +1704,14 @@
 		case itr_latency_low: /* 20k ints/s */
 			if (bytes > 10000) {
 				/* Handle TSO */
-				if (bytes_packets > 8000)
+				if (bytes_per_packet > 8000)
 					nextlatency = itr_latency_bulk;
 				else if ((packets < 10) ||
-				    (bytes_packets > 1200))
+				    (bytes_per_packet > 1200))
 					nextlatency = itr_latency_bulk;
 				else if (packets > 35)
 					nextlatency = itr_latency_lowest;
-			} else if (bytes_packets > 2000) {
+			} else if (bytes_per_packet > 2000) {
 				nextlatency = itr_latency_bulk;
 			} else if (packets < 3 && bytes < 512) {
 				nextlatency = itr_latency_lowest;
@@ -1995,18 +2000,7 @@
 	    (sc->hw.phy.media_type == e1000_media_type_internal_serdes)) {
 		if (sc->hw.mac.type == e1000_82545)
 			fiber_type = IFM_1000_LX;
-		switch (sc->link_speed) {
-		case 10:
-			ifmr->ifm_active |= IFM_10_FL;
-			break;
-		case 100:
-			ifmr->ifm_active |= IFM_100_FX;
-			break;
-		case 1000:
-		default:
-			ifmr->ifm_active |= fiber_type | IFM_FDX;
-			break;
-		}
+		ifmr->ifm_active |= fiber_type | IFM_FDX;
 	} else {
 		switch (sc->link_speed) {
 		case 10:
@@ -2020,11 +2014,10 @@
 			break;
 		}
 	}
-
-	if (sc->link_duplex == FULL_DUPLEX)
-		ifmr->ifm_active |= IFM_FDX;
-	else
-		ifmr->ifm_active |= IFM_HDX;
+		if (sc->link_duplex == FULL_DUPLEX)
+			ifmr->ifm_active |= IFM_FDX;
+		else
+			ifmr->ifm_active |= IFM_HDX;
 }
 
 /*********************************************************************
@@ -2058,26 +2051,6 @@
 		sc->hw.phy.autoneg_advertised = ADVERTISE_1000_FULL;
 		break;
 	case IFM_100_TX:
-		sc->hw.mac.autoneg = DO_AUTO_NEG;
-		if ((ifm->ifm_media & IFM_GMASK) == IFM_FDX) {
-			sc->hw.phy.autoneg_advertised = ADVERTISE_100_FULL;
-			sc->hw.mac.forced_speed_duplex = ADVERTISE_100_FULL;
-		} else {
-			sc->hw.phy.autoneg_advertised = ADVERTISE_100_HALF;
-			sc->hw.mac.forced_speed_duplex = ADVERTISE_100_HALF;
-		}
-		break;
-	case IFM_10_T:
-		sc->hw.mac.autoneg = DO_AUTO_NEG;
-		if ((ifm->ifm_media & IFM_GMASK) == IFM_FDX) {
-			sc->hw.phy.autoneg_advertised = ADVERTISE_10_FULL;
-			sc->hw.mac.forced_speed_duplex = ADVERTISE_10_FULL;
-		} else {
-			sc->hw.phy.autoneg_advertised = ADVERTISE_10_HALF;
-			sc->hw.mac.forced_speed_duplex = ADVERTISE_10_HALF;
-		}
-		break;
-	case IFM_100_FX:
 		sc->hw.mac.autoneg = false;
 		sc->hw.phy.autoneg_advertised = 0;
 		if ((ifm->ifm_media & IFM_GMASK) == IFM_FDX)
@@ -2085,7 +2058,7 @@
 		else
 			sc->hw.mac.forced_speed_duplex = ADVERTISE_100_HALF;
 		break;
-	case IFM_10_FL:
+	case IFM_10_T:
 		sc->hw.mac.autoneg = false;
 		sc->hw.phy.autoneg_advertised = 0;
 		if ((ifm->ifm_media & IFM_GMASK) == IFM_FDX)
@@ -4809,8 +4782,8 @@
 		    sc->stats.ruc + sc->stats.roc +
 		    sc->stats.mpc + sc->stats.cexterr);
 	case IFCOUNTER_OERRORS:
-		return (sc->stats.ecol + sc->stats.latecol +
-		    sc->watchdog_events);
+		return (if_get_counter_default(ifp, cnt) +
+		    sc->stats.ecol + sc->stats.latecol + sc->watchdog_events);
 	default:
 		return (if_get_counter_default(ifp, cnt));
 	}
