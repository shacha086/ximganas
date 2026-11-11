--- dev/igc/if_igc.c.orig	2025-10-17 07:47:25.360037000 +0200
+++ dev/igc/if_igc.c	2025-10-17 09:06:33.000000000 +0200
@@ -909,14 +909,16 @@
     struct tx_ring *txr, struct rx_ring *rxr)
 {
 	struct igc_hw *hw = &sc->hw;
+	unsigned long bytes, bytes_per_packet, packets;
+	unsigned long rxbytes, rxpackets, txbytes, txpackets;
 	u32 neweitr;
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
 
 	neweitr = 0;
@@ -936,17 +938,20 @@
 			goto igc_set_next_eitr;
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
-			bytes_packets = max(bytes_packets,
-			    rxr->rx_bytes/rxr->rx_packets);
-			packets = max(packets, rxr->rx_packets);
+		rxpackets = atomic_load_long(&rxr->rx_packets);
+		if (rxpackets != 0) {
+			bytes = lmax(bytes, rxbytes);
+			bytes_per_packet =
+			    lmax(bytes_per_packet, rxbytes / rxpackets);
+			packets = lmax(packets, rxpackets);
 		}
 
 		/* Latency state machine */
@@ -956,7 +961,7 @@
 			break;
 		case eitr_latency_lowest: /* 70k ints/s */
 			/* TSO and jumbo frames */
-			if (bytes_packets > 8000)
+			if (bytes_per_packet > 8000)
 				nextlatency = eitr_latency_bulk;
 			else if ((packets < 5) && (bytes > 512))
 				nextlatency = eitr_latency_low;
@@ -964,14 +969,14 @@
 		case eitr_latency_low: /* 20k ints/s */
 			if (bytes > 10000) {
 				/* Handle TSO */
-				if (bytes_packets > 8000)
+				if (bytes_per_packet > 8000)
 					nextlatency = eitr_latency_bulk;
 				else if ((packets < 10) ||
-				    (bytes_packets > 1200))
+				    (bytes_per_packet > 1200))
 					nextlatency = eitr_latency_bulk;
 				else if (packets > 35)
 					nextlatency = eitr_latency_lowest;
-			} else if (bytes_packets > 2000) {
+			} else if (bytes_per_packet > 2000) {
 				nextlatency = eitr_latency_bulk;
 			} else if (packets < 3 && bytes < 512) {
 				nextlatency = eitr_latency_lowest;
@@ -2594,8 +2599,8 @@
 		    sc->stats.ruc + sc->stats.roc +
 		    sc->stats.mpc + sc->stats.htdpmc);
 	case IFCOUNTER_OERRORS:
-		return (sc->stats.ecol + sc->stats.latecol +
-		    sc->watchdog_events);
+		return (if_get_counter_default(ifp, cnt) +
+		    sc->stats.ecol + sc->stats.latecol + sc->watchdog_events);
 	default:
 		return (if_get_counter_default(ifp, cnt));
 	}
