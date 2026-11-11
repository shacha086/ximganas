--- gtk/DetailsDialog.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ gtk/DetailsDialog.cc	2025-11-18 21:58:43.000000000 +0100
@@ -52,6 +52,7 @@
 
 #include <algorithm>
 #include <array>
+#include <chrono>
 #include <cstddef>
 #include <cstdlib> // abort()
 #include <iterator>
@@ -611,12 +612,12 @@
 
 [[nodiscard]] std::string get_date_string(time_t t)
 {
-    return t == 0 ? _("N/A") : fmt::format("{:%x}", fmt::localtime(t));
+    return t == 0 ? _("N/A") : fmt::format("{:%x}", *std::localtime(&t));
 }
 
 [[nodiscard]] std::string get_date_time_string(time_t t)
 {
-    return t == 0 ? _("N/A") : fmt::format("{:%c}", fmt::localtime(t));
+    return t == 0 ? _("N/A") : fmt::format("{:%c}", *std::localtime(&t));
 }
 
 } // namespace
