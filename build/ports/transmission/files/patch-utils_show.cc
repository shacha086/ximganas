--- utils/show.cc.orig
+++ utils/show.cc
@@ -166,5 +166,7 @@
 [[nodiscard]] auto toString(time_t now)
 {
-    return now == 0 ? "Unknown" : fmt::format("{:%a %b %d %T %Y}", fmt::localtime(now));
+    auto tm = std::tm{};
+    return now == 0 || localtime_r(&now, &tm) == nullptr ? "Unknown" :
+                                                        fmt::format("{:%a %b %d %T %Y}", tm);
 }
 
