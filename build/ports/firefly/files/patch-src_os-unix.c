--- src/os-unix.c.orig
+++ src/os-unix.c
@@ -282 +282 @@
-    setpgrp();
+    setpgid(0, 0);
