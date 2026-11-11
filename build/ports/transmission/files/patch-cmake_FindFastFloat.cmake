--- cmake/FindFastFloat.cmake.orig	2024-05-31 14:13:31 UTC
+++ cmake/FindFastFloat.cmake
@@ -2,4 +2,4 @@ target_include_directories(FastFloat::fast_float
 
 target_include_directories(FastFloat::fast_float
     INTERFACE
-        ${TR_THIRD_PARTY_SOURCE_DIR}/fast_float/include)
+        ${CMAKE_INSTALL_PREFIX}/include)
