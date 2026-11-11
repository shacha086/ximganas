--- server/drivers/hd44780-low.h.orig	2021-12-20 21:19:25.000000000 +0100
+++ server/drivers/hd44780-low.h	2022-12-10 21:47:06.000000000 +0100
@@ -15,7 +15,7 @@
 #endif
 
 #ifdef HAVE_LIBUSB_1_0
-# include <libusb-1.0/libusb.h>
+# include </usr/include/libusb.h>
 #endif
 
 #if TIME_WITH_SYS_TIME
