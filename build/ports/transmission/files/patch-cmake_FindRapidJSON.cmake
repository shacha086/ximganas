--- cmake/FindRapidJSON.cmake.orig	2025-02-14 03:20:46 UTC
+++ cmake/FindRapidJSON.cmake
@@ -2,7 +2,7 @@
 
 target_include_directories(RapidJSON
     INTERFACE
-        ${TR_THIRD_PARTY_SOURCE_DIR}/rapidjson/include)
+        ${CMAKE_INSTALL_PREFIX}/include/rapidjson)
 
 target_compile_definitions(RapidJSON
     INTERFACE