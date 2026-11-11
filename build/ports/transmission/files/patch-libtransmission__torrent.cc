--- libtransmission/torrent.cc.orig	2025-03-11 00:13:41.000000000 +0100
+++ libtransmission/torrent.cc	2025-11-18 21:40:07.000000000 +0100
@@ -6,6 +6,7 @@
 #include <algorithm>
 #include <array>
 #include <cerrno> // EINVAL
+#include <chrono>
 #include <cstddef> // size_t
 #include <ctime>
 #include <map>
@@ -373,6 +374,8 @@
         return;
     }
 
+    auto const now = tr_time();
+
     auto torrent_dir = tr_pathbuf{ tor->current_dir() };
     tr_sys_path_native_separators(std::data(torrent_dir));
 
@@ -382,7 +385,7 @@
     auto const labels_str = build_labels_string(tor->labels());
     auto const trackers_str = buildTrackersString(tor);
     auto const bytes_downloaded_str = std::to_string(tor->bytes_downloaded_.ever());
-    auto const localtime_str = fmt::format("{:%a %b %d %T %Y%n}", fmt::localtime(tr_time()));
+    auto const localtime_str = fmt::format("{:%a %b %d %T %Y%n}", *std::localtime(&now));
     auto const priority_str = std::to_string(tor->get_priority());
 
     auto const env = std::map<std::string_view, std::string_view>{
