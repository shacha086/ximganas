--- utils/remote.cc.orig
+++ utils/remote.cc
@@ -883,7 +883,12 @@
 std::string_view format_date(std::array<char, N>& buf, time_t now)
 {
+    auto tm = std::tm{};
+    if (localtime_r(&now, &tm) == nullptr)
+    {
+        return {};
+    }
     auto begin = std::data(buf);
-    auto end = fmt::format_to_n(begin, N, "{:%a %b %d %T %Y}", fmt::localtime(now)).out;
+    auto end = fmt::format_to_n(begin, N, "{:%a %b %d %T %Y}", tm).out;
     return { begin, static_cast<size_t>(end - begin) };
 }
 
