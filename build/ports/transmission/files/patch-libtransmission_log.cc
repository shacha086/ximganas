--- libtransmission/log.cc.orig
+++ libtransmission/log.cc
@@ -7,6 +7,7 @@
 #include <cerrno>
 #include <chrono>
 #include <cstddef> // size_t
+#include <ctime>
 #include <iterator> // back_insert_iterator, empty
 #include <mutex>
 #include <optional>
@@ -196,7 +197,12 @@
 std::string_view tr_logGetTimeStr(std::chrono::system_clock::time_point now, char* buf, size_t buflen)
 {
-    auto const now_tm = fmt::localtime(std::chrono::system_clock::to_time_t(now));
+    auto const now_time = std::chrono::system_clock::to_time_t(now);
+    auto now_tm = std::tm{};
+    if (localtime_r(&now_time, &now_tm) == nullptr)
+    {
+        return {};
+    }
     auto const subseconds = now - std::chrono::time_point_cast<std::chrono::seconds>(now);
     auto const [out, len] = fmt::format_to_n(
         buf,
         buflen,
