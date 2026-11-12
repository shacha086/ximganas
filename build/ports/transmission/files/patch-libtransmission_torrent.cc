--- libtransmission/torrent.cc.orig
+++ libtransmission/torrent.cc
@@ -382,7 +382,13 @@
     auto const labels_str = build_labels_string(tor->labels());
     auto const trackers_str = buildTrackersString(tor);
     auto const bytes_downloaded_str = std::to_string(tor->bytes_downloaded_.ever());
-    auto const localtime_str = fmt::format("{:%a %b %d %T %Y%n}", fmt::localtime(tr_time()));
+    auto const now = tr_time();
+    auto tm = std::tm{};
+    if (localtime_r(&now, &tm) == nullptr)
+    {
+        return;
+    }
+    auto const localtime_str = fmt::format("{:%a %b %d %T %Y%n}", tm);
     auto const priority_str = std::to_string(tor->get_priority());
 
     auto const env = std::map<std::string_view, std::string_view>{
