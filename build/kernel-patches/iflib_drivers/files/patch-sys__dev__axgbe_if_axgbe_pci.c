--- dev/axgbe/if_axgbe_pci.c.orig	2025-10-17 07:47:25.083582000 +0200
+++ dev/axgbe/if_axgbe_pci.c	2025-10-17 10:01:49.000000000 +0200
@@ -2368,7 +2368,8 @@
         case IFCOUNTER_OPACKETS:
                 return (pstats->txframecount_gb);
         case IFCOUNTER_OERRORS:
-                return (pstats->txframecount_gb - pstats->txframecount_g);
+                return (if_get_counter_default(ifp, cnt) +
+		    pstats->txframecount_gb - pstats->txframecount_g);
         case IFCOUNTER_IBYTES:
                 return (pstats->rxoctetcount_gb);
         case IFCOUNTER_OBYTES:
