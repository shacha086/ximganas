--- utils/remote.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ utils/remote.cc	2025-09-18 14:21:04.000000000 +0200
@@ -941,7 +941,7 @@
             {
                 if (auto sv = it->value_if<std::string_view>(); sv)
                 {
-                    fmt::print("{:s}{:s}", it == begin ? ", " : "", *sv);
+                    fmt::print("{:s}{:s}", it != begin ? ", " : "", *sv);
                 }
             }
 
