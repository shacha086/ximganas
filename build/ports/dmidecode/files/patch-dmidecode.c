--- dmidecode.c.orig	2023-03-14 17:32:17.000000000 +0100
+++ dmidecode.c	2024-05-08 18:06:37.000000000 +0200
@@ -2225,7 +2225,7 @@
 
 	if (code1 & (1 << 0))
 		pr_attr(attr, "Unknown");
-	else if ((code1 & 0xFE) == 0 && (code2 & 0x07) == 0)
+	else if ((code1 & 0xFE) == 0 && code2 == 0)
 		pr_attr(attr, "None");
 	else
 	{
@@ -6025,17 +6025,25 @@
 		pr_comment("dmidecode %s", VERSION);
 
 	/* Read from dump if so instructed */
+	size = 0x20;
 	if (opt.flags & FLAG_FROM_DUMP)
 	{
 		if (!(opt.flags & FLAG_QUIET))
 			pr_info("Reading SMBIOS/DMI data from file %s.",
 				opt.dumpfile);
-		if ((buf = mem_chunk(0, 0x20, opt.dumpfile)) == NULL)
+		if ((buf = read_file(0, &size, opt.dumpfile)) == NULL)
 		{
 			ret = 1;
 			goto exit_free;
 		}
 
+		/* Truncated entry point can't be processed */
+		if (size < 0x20)
+		{
+			ret = 1;
+			goto done;
+		}
+
 		if (memcmp(buf, "_SM3_", 5) == 0)
 		{
 			if (smbios3_decode(buf, opt.dumpfile, 0))
@@ -6059,7 +6067,7 @@
 	 * contain one of several types of entry points, so read enough for
 	 * the largest one, then determine what type it contains.
 	 */
-	size = 0x20;
+
 	if (!(opt.flags & FLAG_NO_SYSFS)
 	 && (buf = read_file(0, &size, SYS_ENTRY_FILE)) != NULL)
 	{
