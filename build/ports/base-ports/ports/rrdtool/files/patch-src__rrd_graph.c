--- src/rrd_graph.c.orig	2022-03-14 15:30:12.000000000 +0100
+++ src/rrd_graph.c	2023-02-11 15:39:41.000000000 +0100
@@ -3262,7 +3262,7 @@
             im->legendposition == EAST ? im->xOriginLegendY : im->ximg - 4;
         gfx_text(im, xpos, 5, water_color,
                  im->text_prop[TEXT_PROP_WATERMARK].font_desc, im->tabwidth,
-                 -90, GFX_H_LEFT, GFX_V_TOP, "RRDTOOL / TOBI OETIKER");
+                 -90, GFX_H_LEFT, GFX_V_TOP, "XigmaNAS");
     }
     /* graph watermark */
     if (im->watermark && im->watermark[0] != '\0') {
