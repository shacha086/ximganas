--- utils/remote.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ utils/remote.cc	2025-11-18 21:48:03.000000000 +0100
@@ -7,6 +7,7 @@
 #include <array>
 #include <cctype> /* isspace */
 #include <cmath> // floor
+#include <chrono>
 #include <cstdint> // int64_t
 #include <cstdio>
 #include <cstdlib>
@@ -883,7 +884,7 @@
 std::string_view format_date(std::array<char, N>& buf, time_t now)
 {
     auto begin = std::data(buf);
-    auto end = fmt::format_to_n(begin, N, "{:%a %b %d %T %Y}", fmt::localtime(now)).out;
+    auto end = fmt::format_to_n(begin, N, "{:%a %b %d %T %Y}", *std::localtime(&now)).out;
     return { begin, static_cast<size_t>(end - begin) };
 }
 
@@ -941,7 +942,7 @@
             {
                 if (auto sv = it->value_if<std::string_view>(); sv)
                 {
-                    fmt::print("{:s}{:s}", it == begin ? ", " : "", *sv);
+                    fmt::print("{:s}{:s}", it != begin ? ", " : "", *sv);
                 }
             }
 
