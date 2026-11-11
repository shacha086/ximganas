--- sys/dev/ahci/ahci.c.orig	2025-09-23 21:58:00.011708000 +0200
+++ sys/dev/ahci/ahci.c	2025-09-27 01:54:10.000000000 +0200
@@ -2177,7 +2177,7 @@
 	}
 	xpt_setup_ccb(&ccb->ccb_h, ch->hold[i]->ccb_h.path,
 	    ch->hold[i]->ccb_h.pinfo.priority);
-	if (ccb->ccb_h.func_code == XPT_ATA_IO) {
+	if (ch->hold[i]->ccb_h.func_code == XPT_ATA_IO) {
 		/* READ LOG */
 		ccb->ccb_h.recovery_type = RECOVERY_READ_LOG;
 		ccb->ccb_h.func_code = XPT_ATA_IO;
