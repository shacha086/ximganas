/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-cpu_temp_${GRAPH_NAME}.png 2>> /tmp/rrdgraphs-error.log \
--start $START_TIME \
--end -0h \
--title "$TITLE_STRING" \
--vertical-label "Temperature [°C]" \
$LEFT_AXIS_FORMAT "-a" "PNG" \
"-h ${GRAPH_H}" \
"-w ${GRAPH_W}" \
--slope-mode $BACKGROUND $EXTENDED_OPTIONS \
"DEF:c00=$STORAGE_PATH/rrd/cpu_temp.rrd:core0:AVERAGE" \
"LINE1:c00#00CF00:Core  0" \
"GPRINT:c00:MIN:Min\\: %5.1lf" \
"GPRINT:c00:MAX:Max\\: %5.1lf" \
"GPRINT:c00:AVERAGE:Avg\\: %5.1lf" \
"GPRINT:c00:LAST:Last\\: %5.1lf" \
"COMMENT:\n" \
"TEXTALIGN:right" "COMMENT:Last Update\: $LAST_UPDATE"
