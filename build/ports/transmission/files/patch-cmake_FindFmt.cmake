--- cmake/FindFmt.cmake.orig	2024-05-31 14:14:11 UTC
+++ cmake/FindFmt.cmake
@@ -2,7 +2,7 @@ target_include_directories(fmt::fmt-header-only
 
 target_include_directories(fmt::fmt-header-only
     INTERFACE
-        ${TR_THIRD_PARTY_SOURCE_DIR}/fmt/include)
+        ${CMAKE_INSTALL_PREFIX}/include) 
 
 target_compile_definitions(fmt::fmt-header-only
     INTERFACE
