--- dev/e1000/e1000_phy.c.orig	2025-10-17 07:47:25.251583000 +0200
+++ dev/e1000/e1000_phy.c	2025-10-17 12:38:32.000000000 +0200
@@ -1707,10 +1707,9 @@
 		 * autonegotiation.
 		 */
 		ret_val = e1000_copper_link_autoneg(hw);
-		if (ret_val && !hw->mac.forced_speed_duplex)
+		if (ret_val)
 			return ret_val;
-	}
-	if (!hw->mac.autoneg || (ret_val && hw->mac.forced_speed_duplex)) {
+	} else {
 		/* PHY will be set to 10H, 10F, 100H or 100F
 		 * depending on user settings.
 		 */
