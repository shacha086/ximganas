--- libtransmission/session-alt-speeds.cc.orig
+++ libtransmission/session-alt-speeds.cc
@@ -78,7 +78,11 @@
 [[nodiscard]] bool tr_session_alt_speeds::is_active_minute(time_t time) const noexcept
 {
-    auto const tm = fmt::localtime(time);
+    auto tm = std::tm{};
+    if (localtime_r(&time, &tm) == nullptr)
+    {
+        return false;
+    }
 
     size_t minute_of_the_week = (tm.tm_wday * MinutesPerDay) + (tm.tm_hour * MinutesPerHour) + tm.tm_min;
 
     if (minute_of_the_week >= MinutesPerWeek) /* leap minutes? */
