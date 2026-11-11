--- dev/ixgbe/if_ix.c.orig	2025-10-17 07:47:25.000000000 +0200
+++ dev/ixgbe/if_ix.c	2025-10-17 21:55:11.000000000 +0200
@@ -174,6 +174,7 @@
    int);
 static void ixgbe_if_queues_free(if_ctx_t);
 static void ixgbe_if_timer(if_ctx_t, uint16_t);
+static const char *ixgbe_link_speed_to_str(u32 link_speed);
 static void ixgbe_if_update_admin_status(if_ctx_t);
 static void ixgbe_if_vlan_register(if_ctx_t, u16);
 static void ixgbe_if_vlan_unregister(if_ctx_t, u16);
@@ -1296,8 +1297,6 @@
 		return (0);
 	case IFCOUNTER_IQDROPS:
 		return (sc->iqdrops);
-	case IFCOUNTER_OQDROPS:
-		return (0);
 	case IFCOUNTER_IERRORS:
 		return (sc->ierrors);
 	default:
@@ -3858,6 +3857,35 @@
 } /* ixgbe_if_stop */
 
 /************************************************************************
+ * ixgbe_link_speed_to_str - Convert link speed to string
+ *
+ *   Helper function to convert link speed constants to human-readable
+ *   string representations in conventional Gbps or Mbps.
+ ************************************************************************/
+static const char *
+ixgbe_link_speed_to_str(u32 link_speed)
+{
+    switch (link_speed) {
+    case IXGBE_LINK_SPEED_10GB_FULL:
+        return "10 Gbps";
+    case IXGBE_LINK_SPEED_5GB_FULL:
+        return "5 Gbps";
+    case IXGBE_LINK_SPEED_2_5GB_FULL:
+        return "2.5 Gbps";
+    case IXGBE_LINK_SPEED_1GB_FULL:
+        return "1 Gbps";
+    case IXGBE_LINK_SPEED_100_FULL:
+        return "100 Mbps";
+    case IXGBE_LINK_SPEED_10_FULL:
+        return "10 Mbps";
+    default:
+        return "Unknown";
+    }
+}
+
+ /* ixgbe_link_speed_to_str */
+
+/************************************************************************
  * ixgbe_update_link_status - Update OS on link state
  *
  * Note: Only updates the OS on the cached link state.
@@ -3873,9 +3901,9 @@
 	if (sc->link_up) {
 		if (sc->link_active == false) {
 			if (bootverbose)
-				device_printf(dev, "Link is up %d Gbps %s \n",
-				    ((sc->link_speed == 128) ? 10 : 1),
-				    "Full Duplex");
+				device_printf(dev,
+				    "Link is up %s Full Duplex\n",
+				    ixgbe_link_speed_to_str(sc->link_speed));
 			sc->link_active = true;
 			/* Update any Flow Control changes */
 			ixgbe_fc_enable(&sc->hw);
