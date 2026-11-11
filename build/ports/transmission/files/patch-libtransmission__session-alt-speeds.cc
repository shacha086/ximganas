--- libtransmission/session-alt-speeds.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ libtransmission/session-alt-speeds.cc	2025-11-18 21:28:50.000000000 +0100
@@ -3,6 +3,7 @@
 // or any future license endorsed by Mnemosyne LLC.
 // License text can be found in the licenses/ folder.
 
+#include <chrono>
 #include <cstddef> // size_t
 #include <ctime>
 #include <utility>
@@ -78,7 +79,7 @@
 
 [[nodiscard]] bool tr_session_alt_speeds::is_active_minute(time_t time) const noexcept
 {
-    auto const tm = fmt::localtime(time);
+    auto const tm = *std::localtime(&time);
 
     size_t minute_of_the_week = (tm.tm_wday * MinutesPerDay) + (tm.tm_hour * MinutesPerHour) + tm.tm_min;
 
