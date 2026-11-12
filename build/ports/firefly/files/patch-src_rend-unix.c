--- src/rend-unix.c.orig
+++ src/rend-unix.c
@@ -96 +96 @@
-    setpgrp();
+    setpgid(0, 0);
