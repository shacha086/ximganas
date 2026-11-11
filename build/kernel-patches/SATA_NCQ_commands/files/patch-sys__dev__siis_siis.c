--- sys/dev/siis/siis.c.orig	2025-09-23 21:58:00.596284000 +0200
+++ sys/dev/siis/siis.c	2025-09-27 01:58:36.000000000 +0200
@@ -1396,7 +1396,7 @@
 	}
 	xpt_setup_ccb(&ccb->ccb_h, ch->hold[i]->ccb_h.path,
 	    ch->hold[i]->ccb_h.pinfo.priority);
-	if (ccb->ccb_h.func_code == XPT_ATA_IO) {
+	if (ch->hold[i]->ccb_h.func_code == XPT_ATA_IO) {
 		/* READ LOG */
 		ccb->ccb_h.recovery_type = RECOVERY_READ_LOG;
 		ccb->ccb_h.func_code = XPT_ATA_IO;
