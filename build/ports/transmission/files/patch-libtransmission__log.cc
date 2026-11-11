--- libtransmission/log.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ libtransmission/log.cc	2025-11-18 21:22:10.000000000 +0100
@@ -14,6 +14,10 @@
 #include <string_view>
 #include <utility>
 
+#ifdef _WIN32
+#include <windows.h> // GetTimeZoneInformation
+#endif
+
 #ifdef __ANDROID__
 #include <android/log.h>
 #endif
@@ -26,7 +30,7 @@
 #include "libtransmission/file.h"
 #include "libtransmission/log.h"
 #include "libtransmission/tr-assert.h"
-#include "libtransmission/tr-strbuf.h"
+#include "libtransmission/tr-macros.h"
 #include "libtransmission/utils.h"
 
 using namespace std::literals;
@@ -194,17 +198,37 @@
 
 // ---
 
-std::string_view tr_logGetTimeStr(std::chrono::system_clock::time_point now, char* buf, size_t buflen)
+std::string_view tr_logGetTimeStr(std::chrono::system_clock::time_point const now, char* const buf, size_t const buflen)
 {
-    auto const now_tm = fmt::localtime(std::chrono::system_clock::to_time_t(now));
-    auto const subseconds = now - std::chrono::time_point_cast<std::chrono::seconds>(now);
-    auto const [out, len] = fmt::format_to_n(
-        buf,
-        buflen,
-        "{0:%FT%T.}{1:0>3%Q}{0:%z}",
-        now_tm,
-        std::chrono::duration_cast<std::chrono::milliseconds>(subseconds));
-    return { buf, len };
+    auto* walk = buf;
+    auto const now_time_t = std::chrono::system_clock::to_time_t(now);
+    auto const now_tm = *std::localtime(&now_time_t);
+    walk = fmt::format_to_n(
+               walk,
+               buflen,
+               "{0:%FT%R:}{1:%S}" TR_IF_WIN32("", "{0:%z}"),
+               now_tm,
+               std::chrono::time_point_cast<std::chrono::milliseconds>(now))
+               .out;
+#ifdef _WIN32
+    if (auto tz_info = TIME_ZONE_INFORMATION{}; GetTimeZoneInformation(&tz_info) != TIME_ZONE_ID_INVALID)
+    {
+        // https://learn.microsoft.com/en-us/windows/win32/api/timezoneapi/nf-timezoneapi-gettimezoneinformation
+        // All translations between UTC time and local time are based on the following formula:
+        //     UTC = local time + bias
+        // The bias is the difference, in minutes, between UTC time and local time.
+        auto const offset = tz_info.Bias < 0 ? -tz_info.Bias : tz_info.Bias;
+        walk = fmt::format_to_n(
+                   walk,
+                   buflen - (walk - buf),
+                   "{:c}{:02d}{:02d}",
+                   tz_info.Bias < 0 ? '+' : '-',
+                   offset / 60,
+                   offset % 60)
+                   .out;
+    }
+#endif
+    return { buf, static_cast<size_t>(walk - buf) };
 }
 
 std::string_view tr_logGetTimeStr(char* buf, size_t buflen)
