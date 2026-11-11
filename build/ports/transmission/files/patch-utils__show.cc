--- utils/show.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ utils/show.cc	2025-11-18 21:53:01.000000000 +0100
@@ -165,7 +165,7 @@
 
 [[nodiscard]] auto toString(time_t now)
 {
-    return now == 0 ? "Unknown" : fmt::format("{:%a %b %d %T %Y}", fmt::localtime(now));
+    return now == 0 ? "Unknown" : fmt::format("{:%a %b %d %T %Y}", *std::localtime(&now));
 }
 
 bool compareSecondField(std::string_view l, std::string_view r)
