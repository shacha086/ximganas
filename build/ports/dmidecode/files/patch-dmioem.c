--- dmioem.c.orig	2023-03-14 17:32:17.000000000 +0100
+++ dmioem.c	2024-03-29 21:16:31.000000000 +0100
@@ -1094,7 +1094,8 @@
 			 *  0x06  | Manufacture|STRING | DIMM Manufacturer
 			 *  0x07  | Part Number|STRING | DIMM Manufacturer's Part Number
 			 *  0x08  | Serial Num |STRING | DIMM Vendor Serial Number
-			 *  0x09  | Spare Part |STRING | DIMM Spare Part Number
+			 *  0x09  | Man Date   | BYTE  | DIMM Manufacture Date (YEAR) in BCD
+			 *  0x0A  | Man Date   | BYTE  | DIMM Manufacture Date (WEEK) in BCD
 			 */
 			if (gen < G9) return 0;
 			pr_handle_name("%s DIMM Vendor Information", company);
@@ -1105,8 +1106,9 @@
 			pr_attr("DIMM Manufacturer Part Number", "%s", dmi_string(h, data[0x07]));
 			if (h->length < 0x09) break;
 			pr_attr("DIMM Vendor Serial Number", "%s", dmi_string(h, data[0x08]));
-			if (h->length < 0x0A) break;
-			pr_attr("DIMM Spare Part Number", "%s", dmi_string(h, data[0x09]));
+			if (h->length < 0x0B) break;
+			if (WORD(data + 0x09))
+				pr_attr("DIMM Manufacture Date", "20%02x-W%02x", data[0x09], data[0x0A]);
 			break;
 
 		case 238:
