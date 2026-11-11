# DS: $charge:$load:$bvoltage:$ovoltage:$ivoltage:$runtime:$OL:$OF:$OB:$CG
/usr/local/bin/rrdtool graph $WORKING_DIR/rrd/rrd-${GRAPH}_${GRAPH_NAME}.png \
"-v UPS Usage" \
"-s" "$START_TIME" \
"-t" "$TITLE_STRING" \
$BACKGROUND \
"-a" "PNG" \
"-h ${GRAPH_H}" \
"-w ${GRAPH_W}" \
"--slope-mode" \
"--alt-autoscale-max" \
"DEF:charge=$STORAGE_PATH/rrd/ups.rrd:charge:AVERAGE" \
"DEF:load=$STORAGE_PATH/rrd/ups.rrd:load:AVERAGE" \
"DEF:bvoltage=$STORAGE_PATH/rrd/ups.rrd:bvoltage:AVERAGE" \
"DEF:ovoltage=$STORAGE_PATH/rrd/ups.rrd:ovoltage:AVERAGE" \
"DEF:ivoltage=$STORAGE_PATH/rrd/ups.rrd:ivoltage:AVERAGE" \
"DEF:runtime=$STORAGE_PATH/rrd/ups.rrd:runtime:AVERAGE" \
"DEF:OL=$STORAGE_PATH/rrd/ups.rrd:OL:AVERAGE" \
"DEF:OF=$STORAGE_PATH/rrd/ups.rrd:OF:AVERAGE" \
"DEF:OB=$STORAGE_PATH/rrd/ups.rrd:OB:AVERAGE" \
"DEF:CG=$STORAGE_PATH/rrd/ups.rrd:CG:AVERAGE" \
"AREA:charge#64E464:Battery capacity    [%]" \
"GPRINT:charge:MIN:Min\\:%6.1lf" \
"GPRINT:charge:MAX:Max\\:%6.1lf" \
"GPRINT:charge:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:charge:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE2:bvoltage#0000A0:Battery voltage     [V]" \
"GPRINT:bvoltage:MIN:Min\\:%6.1lf" \
"GPRINT:bvoltage:MAX:Max\\:%6.1lf" \
"GPRINT:bvoltage:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:bvoltage:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE1:load#FF0000:Battery load        [%]" \
"GPRINT:load:MIN:Min\\:%6.1lf" \
"GPRINT:load:MAX:Max\\:%6.1lf" \
"GPRINT:load:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:load:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE1:runtime#800080:Remaining runtime [min]" \
"GPRINT:runtime:MIN:Min\\:%6.1lf" \
"GPRINT:runtime:MAX:Max\\:%6.1lf" \
"GPRINT:runtime:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:runtime:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE1:ivoltage#5050F0:Input voltage       [V]" \
"GPRINT:ivoltage:MIN:Min\\:%6.1lf" \
"GPRINT:ivoltage:MAX:Max\\:%6.1lf" \
"GPRINT:ivoltage:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:ivoltage:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"LINE2:ovoltage#FFFF00:Output voltage      [V]" \
"GPRINT:ovoltage:MIN:Min\\:%6.1lf" \
"GPRINT:ovoltage:MAX:Max\\:%6.1lf" \
"GPRINT:ovoltage:AVERAGE:Avg\\:%6.1lf" \
"GPRINT:ovoltage:LAST:Last\\:%6.1lf" \
"COMMENT:\n" \
"COMMENT:UPS status\\:" \
"AREA:OL#009900:Online" \
"AREA:OB#CC0000:On battery" \
"AREA:CG#FFA500:Charging" \
"AREA:OF#FF00FF:Offline" \
"--lower-limit" "-9" \
"--rigid" \
"TEXTALIGN:right" "COMMENT:Last Update\: $LAST_UPDATE"
