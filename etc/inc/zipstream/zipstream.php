<?php
/*
	Part of XigmaNAS® (https://www.xigmanas.com).
	Copyright © 2018-2025 XigmaNAS® <info@xigmanas.com>.
	All rights reserved.

	ZipStream - Streamed, dynamically generated zip archives.
	by Paul Duncan <pabs@pablotron.org>

 	Copyright (C) 2007-2009 Paul Duncan <pabs@pablotron.org>

	Permission is hereby granted, free of charge, to any person obtaining
	a copy of this software and associated documentation files (the
	"Software"), to deal in the Software without restriction, including
	without limitation the rights to use, copy, modify, merge, publish,
	distribute, sublicense, and/or sell copies of the Software, and to
	permit persons to whom the Software is furnished to do so, subject to
	the following conditions:

	The above copyright notice and this permission notice shall be
	included in all copies or substantial portions of the of the Software.

	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
	EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
	MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT.
	IN NO EVENT SHALL THE AUTHORS BE LIABLE FOR ANY CLAIM, DAMAGES OR
	OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE,
	ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
	OTHER DEALINGS IN THE SOFTWARE.

	Requirements: PHP version 5.1.2 or newer.

	Usage:

	Streaming zip archives is a simple, three-step process:

	1.	Create the zip stream:

		$zip = new ZipStream('example.zip');

	2.	Add one or more files to the archive:

		# add first file
		$data = file_get_contents('some_file.gif');
		$zip->add_file('some_file.gif',$data);

		# add second file
		$data = file_get_contents('some_file.gif');
		$zip->add_file('another_file.png',$data);

	3.	Finish the zip stream:

		$zip->finish();

	You can also add an archive comment, add comments to individual files,
	and adjust the timestamp of files. See the API documentation for each
	method below for additional information.

	Example:

	# create a new zip stream object
		$zip = new ZipStream('some_files.zip');

	# list of local files
		$files = ['foo.txt','bar.jpg'];

	# read and add each file to the archive
		foreach ($files as $path)
			$zip->add_file($path,file_get_contents($path));

	# write archive footer to stream
		$zip->finish();
 */
namespace zipstream;

class zipstream {
	const VERSION         = '0.2.2.modified';
	const MAXINT32        = 0xffffffff;
	const SIG_LFH         = 0x04034b50; // LOCAL_FILE_HEADER
//	const SIG_AED         = 0x08064b50; // ARCHIVE_EXTRA_DATA
	const SIG_CFH         = 0x02014b50; // CENTRAL_FILE_HEADER
//	const SIG_DH          = 0x05054b50; // DIGITAL_HEADER
//	const SIG_ZIP64_EOCDR = 0x06064b50; // ZIP64_END_OF_CENTRAL_DIR_RECORD
//	const SIG_ZIP64_EOCDL = 0x07064b50; // ZIP64_END_OF_CENTRAL_DIR_LOCATOR
	const SIG_EOCD        = 0x06054b50; // END_OF_CENTRAL_DIR

	protected ?string $x_output_name = null;
	protected string $x_comment = '';
	protected int $x_large_file_size = 20 * 1024 *1024;
	protected string $x_large_file_method = 'deflate';
	protected bool $x_send_http_headers = false;
	protected string $x_content_type = 'application/x-zip';
	protected string $x_content_disposition = 'attachment';
	protected bool $x_need_headers = false;
	protected array $x_files = [];
	protected int $x_cdr_ofs = 0;
	protected int $x_ofs = 0;
	protected $output_stream;
/**
 *	Create a new ZipStream object.
 *
 *	Archive Options:
 *		comment             Comment for this archive.
 *		content_type        HTTP Content-Type. Defaults to 'application/x-zip'.
 *		content_disposition HTTP Content-Disposition. Defaults to
 *		                    'attachment; filename=\"FILENAME\"', where
 *		                    FILENAME is the specified filename.
 *		large_file_size     Size, in bytes, of the largest file to try
 *		                    and load into memory (used by
 *		                    add_file_from_path()). Large files may also
 *		                    be compressed differently; see the
 *		                    'large_file_method' option.
 *		large_file_method   How to handle large files. Legal values are
 *		                    'deflate' (the default), or 'store'. Store
 *		                    sends the file raw and is significantly
 *		                    faster, while 'deflate' compresses the file
 *		                    and is much, much slower. Note that deflate
 *		                    must compress the file twice and extremely
 *		                    slow.
 *		send_http_headers   Boolean indicating whether or not to send
 *		                    the HTTP headers for this file.
 *
 *	Note that content_type and content_disposition do nothing if you are
 *	not sending HTTP headers.
 *
 *	Large File Support:
 *	By default, the method add_file_from_path() will send send files
 *	larger than 20 megabytes along raw rather than attempting to
 *	compress them. You can change both the maximum size and the
 *	compression behavior using the large_file_* options above, with the
 *	following caveats:

 *	*	For "small" files (e.g. files smaller than large_file_size), the
 *		memory use can be up to twice that of the actual file. In other
 *		words, adding a 10 megabyte file to the archive could potentially
 *		occupty 20 megabytes of memory.
 *
 *	*	Enabling compression on large files (e.g. files larger than
 *		large_file_size) is extremely slow, because ZipStream has to pass
 *		over the large file once to calculate header information, and then
 *		again to compress and send the actual data.
 *
 *	Notes:
 *	If you do not set a filename, then this library _DOES NOT_ send HTTP
 *	headers by default. This behavior is to allow software to send its
 *	own headers (including the filename), and still use this library.
 *
 *	@param string|null $name Name of output file (optional).
 *	@param array|null $opt Array of archive options (optional, see "Archive Options").
 */
	public function __construct(?string $name = null,?array $opt = []) {
//		save options
		$this->x_output_name = $name;
		$key = 'send_http_headers';
		if(array_key_exists($key,$opt)):
			if(is_bool($opt[$key])):
				$this->x_send_http_headers = $opt[$key];
			endif;
		endif;
		$key = 'large_file_size';
		if(array_key_exists($key,$opt)):
			if(is_int($opt[$key])):
				if($opt[$key] >= 0):
					$this->x_large_file_size = $opt[$key];
				endif;
			endif;
		endif;
		$key = 'large_file_method';
		if(array_key_exists($key,$opt)):
			if(is_string($opt[$key])):
				if(preg_match('^(deflate|store)$',$key) === 1):
					$this->x_large_file_method = $opt[$key];
				endif;
			endif;
		endif;
		$key = 'content_type';
		if(array_key_exists($key,$opt)):
			if(is_string($opt[$key])):
				$this->x_content_type = $opt[$key];
			endif;
		endif;
		$key = 'content_disposition';
		if(array_key_exists($key,$opt)):
			if(is_string($opt[$key])):
				$this->x_content_disposition = $opt[$key];
			endif;
		endif;
		$key = 'comment';
		if(array_key_exists($key,$opt)):
			if(is_string($opt[$key])):
				$this->x_comment = $opt[$key];
			endif;
		endif;
		if(!is_null($name) || $this->x_send_http_headers):
			$this->x_need_headers = true;
		else:
			$this->x_need_headers = false;
		endif;
		$this->output_stream = fopen('php://output','b');
	}
	public function __destruct() {
		fclose($this->output_stream);
	}
/*
 *	add_file - add a file to the archive
 *
 *	File Options:
 *		time	Last-modified timestamp (seconds since the epoch) of this file. Defaults to the current time.
 *		comment	Comment related to this file.
 *
 *	Examples:
 *		# add a file named 'foo.txt'
 *			$data = file_get_contents('foo.txt');
 *			$zip->add_file('foo.txt',$data);
 *
 *		# add a file named 'bar.jpg' with a comment and a last-modified time of two hours ago
 *			$data = file_get_contents('bar.jpg');
 *			$zip->add_file('bar.jpg',$data,['time' => time() - 2 * 3600,'comment' => 'this is a comment about bar.jpg']);
 *
 *	@param string $name path of file in archive (including directory).
 *	@param string $data contents of file
 *	@param array $opt array of options for file (optional, see "File Options").
 */
	public function add_file(string $name,string $data,array $opt = []) {
//		compress data
		$zdata = gzdeflate($data);
//		calculate header attributes
		$crc = crc32($data);
		$zlen = strlen($zdata);
		$len = strlen($data);
		$meth = 0x08;
/*
		if(($zlen > static::MAXINT32) || ($len > static::MAXINT32) || ($this->x_ofs > static::MAXINT32)):
//			zip64
		endif;
 */
//		send file header
		$this->add_file_header($name,$opt,$meth,$crc,$zlen,$len);
//		send data
		$this->send($zdata);
	}
/**
 * 	add_file_from_path - add a file at path to the archive.
 *
 * 	Note that large files may be compresed differently than smaller
 * 	files; see the "Large File Support" section above for more
 * 	information.
 *
 * 	File Options:
 * 		time	-	Last-modified timestamp (seconds since the epoch) of
 * 					this file. Defaults to the current time.
 * 		comment	-	Comment related to this file.
 *
 * 	Examples:
 *
 * 		# add a file named 'foo.txt' from the local file '/tmp/foo.txt'
 * 			$zip->add_file_from_path('foo.txt','/tmp/foo.txt');
 *
 * 		# add a file named 'bigfile.rar' from the local file '/usr/share/bigfile.rar' with a comment and a last-modified time of two hours ago
 * 			$path = '/usr/share/bigfile.rar';
 * 			$zip->add_file_from_path('bigfile.rar', $path,
 * 			['time' => time() - 2 * 3600,'comment' => 'this is a comment about bar.jpg']);
 *
 *	@param string $name name of file in archive (including directory path).
 *	@param string $path path to file on disk (note: paths should be encoded using UNIX-style forward slashes -- e.g '/path/to/some/file').
 *	@param array $opt array of options for file (optional, see "File Options").
 */
	public function add_file_from_path(string $name,string $path,array $opt = []) {
		if($this->is_large_file($path)):
//			file is too large to be read into memory; add progressively
			$this->add_large_file($name,$path,$opt);
		else:
//			file is small enough to read into memory; read file contents and handle with add_file()
			$data = file_get_contents($path);
			$this->add_file($name,$data,$opt);
		endif;
	}
/**
 *	finish - Write zip footer to stream.
 *
 *	Example:
 *		# add a list of files to the archive
 *			$files = ['foo.txt','bar.jpg'];
 *			foreach ($files as $path):
 *				$zip->add_file($path,file_get_contents($path));
 *			endforeach;
 *		# write footer to stream
 *			$zip->finish();
 */
	public function finish() {
//		add trailing cdr record
		$this->add_cdr();
		$this->clear();
	}
/**
 *	Create and send zip header for this file.
 *	@param string $name
 *	@param array $opt
 *	@param string $meth
 *	@param int $crc
 *	@param int $zlen
 *	@param int $len
 */
	private function add_file_header($name,$opt,$meth,$crc,$zlen,$len) {
//	strip leading slashes from file name (fixes bug in windows archive viewer)
		$name = preg_replace('/^\\/+/','',$name);
//		calculate name length
		$nlen = strlen($name);
//		create dos timestamp
		$opt['time'] ??= time();
		$dts = $this->dostime($opt['time']);
//		build file header
		$fields = [                // (from V.A of APPNOTE.TXT)
			['V',static::SIG_LFH], // local file header signature
			['v',(6 << 8) + 3],    // version needed to extract
			['v',0x00],            // general purpose bit flag
			['v',$meth],           // compression method (deflate or store)
			['V',$dts],            // dos timestamp
			['V',$crc],            // crc32 of data
			['V',$zlen],           // compressed data length
			['V',$len],            // uncompressed data length
			['v',$nlen],           // filename length
			['v',0],               // extra data len
		];
//		pack fields and calculate "total" length
		$ret = $this->pack_fields($fields);
		$cdr_len = strlen($ret) + $nlen + $zlen;
//		print header and filename
		$this->send($ret . $name);
//		add to central directory record and increment offset
		$this->add_to_cdr($name,$opt,$meth,$crc,$zlen,$len,$cdr_len);
	}
/**
 *	Add a large file from the given path.
 *	@param string $name
 *	@param string $path
 *	@param array $opt
 */
	private function add_large_file($name,$path,$opt = []) {
		$st = stat($path);
		$block_size = 1048576; # process in 1 megabyte chunks
		$algo = 'crc32b';
//		calculate header attributes
		$zlen = $len = $st['size'];
		switch($this->x_large_file_method):
			case 'store':
//				store method
				$meth = 0x00;
				$crc = unpack('V',hash_file($algo,$path,true));
				$crc = $crc[1];
				break;
			case 'deflate':
//				deflate method
				$meth = 0x08;
//				open file, calculate crc and compressed file length
				$fh = fopen($path,'rb');
				$hash_ctx = hash_init($algo);
				$zlen = 0;
//				read each block, update crc and zlen
				while($data = fgets($fh,$block_size)):
					hash_update($hash_ctx,$data);
					$data = gzdeflate($data);
					$zlen += strlen($data);
				endwhile;
//				close file and finalize crc
				fclose($fh);
				$crc = unpack('V',hash_final($hash_ctx,true));
				$crc = $crc[1];
				break;
		endswitch;
//		send file header
		$this->add_file_header($name,$opt,$meth,$crc,$zlen,$len);
//		open input file
		$fh = fopen($path,'rb');
//		send file blocks
		while($data = fgets($fh,$block_size)):
			if($this->x_large_file_method === 'deflate'):
				$data = gzdeflate($data);
			endif;
//			send data
			$this->send($data);
		endwhile;
//		close input file
		fclose($fh);
	}
/**
 *
 *	@param string $path
 *	@return bool
 */
	private function is_large_file($path): bool {
		$st = stat($path);
		return ($st['size'] > $this->x_large_file_size);
	}
/**
 *	Save file attributes for trailing CDR record.
 * 	@param type $name
 * 	@param type $opt
 * 	@param type $meth
 * 	@param type $crc
 * 	@param type $zlen
 * 	@param type $len
 * 	@param type $rec_len
 */
	private function add_to_cdr($name,$opt,$meth,$crc,$zlen,$len,$rec_len) {
		$this->x_files[] = [$name,$opt,$meth,$crc,$zlen,$len,$this->x_ofs];
		$this->x_ofs += $rec_len;
	}
/**
 * 	Send CDR record for specified file.
 * 	@param array $args
 */
	private function add_cdr_file($args) {
		[$name,$opt,$meth,$crc,$zlen,$len,$ofs] = $args;
//		get attributes
		$comment = $opt['comment'] ?? '';
		$uts = $opt['time'] ?? 0;
//		get dos timestamp
		$dts = $this->dostime($uts);
		$fields = [                 // (from V,F of APPNOTE.TXT)
			['V',static::SIG_CFH],  // central file header signature
			['v',(6 << 8) + 3],     // version made by
			['v',(6 << 8) + 3],     // version needed to extract
			['v',0x00],             // general purpose bit flag
			['v',$meth],            // compresion method (deflate or store)
			['V',$dts],             // dos timestamp
			['V',$crc],             // crc32 of data
			['V',$zlen],            // compressed data length
			['V',$len],             // uncompressed data length
			['v',strlen($name)],    // filename length
			['v',0],                // extra data len
			['v',strlen($comment)], // file comment length
			['v',0],                // disk number start
			['v',0],                // internal file attributes
			['V',32],               // external file attributes
			['V',$ofs]              // relative offset of local header
		];
//		pack fields, then append name and comment
		$ret = $this->pack_fields($fields) . $name . $comment;
		$this->send($ret);
//		increment cdr offset
		$this->x_cdr_ofs += strlen($ret);
	}
/**
 * 	Send CDR EOF (Central Directory Record End-of-File) record.
 * 	@param array $opt
 */
	private function add_cdr_eof() {
		$num = count($this->x_files);
		$cdr_len = $this->x_cdr_ofs;
		$cdr_ofs = $this->x_ofs;
//		grab comment (if specified)
		$comment = $this->x_comment;
		$fields = [                 // (from V,F of APPNOTE.TXT)
			['V',static::SIG_EOCD], // end of central file header signature
			['v',0x00],             // this disk number
			['v',0x00],             // number of disk with cdr
			['v',$num],             // number of entries in the cdr on this disk
			['v',$num],             // number of entries in the cdr
			['V',$cdr_len],         // cdr size
			['V',$cdr_ofs],         // cdr ofs
			['v',strlen($comment)]  // zip file comment length
		];
		$ret = $this->pack_fields($fields) . $comment;
		$this->send($ret);
	}
/**
 * 	Add CDR (Central Directory Record) footer.
 * 	@param array $opt
 */
	private function add_cdr() {
		foreach($this->x_files as $file):
			$this->add_cdr_file($file);
		endforeach;
		$this->add_cdr_eof();
	}
/**
 * 	Clear all internal variables. Note that the stream object is not usable after this.
 */
	private function clear() {
		$this->x_files = [];
		$this->x_ofs = 0;
		$this->x_cdr_ofs = 0;
	}
/**
 * Send HTTP headers for this stream.
 */
	private function send_http_headers() {
//		get content disposition
		if(is_null($this->x_output_name)):
			$disposition = $this->x_content_disposition;
		else:
			$disposition = $this->x_content_disposition . '; filename="' . $this->x_output_name . '"';
		endif;
		$headers = [
			'Content-Type' => $this->x_content_type,
			'Content-Disposition' => $disposition,
			'Pragma' => 'public',
			'Cache-Control' => 'public, must-revalidate',
			'Content-Transfer-Encoding' => 'binary',
		];
		foreach($headers as $key => $val):
			header("$key: $val");
		endforeach;
	}
/**
 * 	Send string, sending HTTP headers if necessary.
 * 	@param string $data
 */
	private function send(string $data) {
		if($this->x_need_headers):
			$this->x_need_headers = false;
			$this->send_http_headers();
		endif;
		fwrite($this->output_stream,$data);
		unset($data);
		if(ob_get_length() !== false):
			ob_flush();
		endif;
		flush();
	}
/**
 * 	Convert a UNIX timestamp to a DOS timestamp.
 * 	@param int $when
 * 	@return int
 */
	private function dostime($when = 0) {
//	get date array for timestamp
		$d = getdate($when);
//		set lower-bound on dates
		if($d['year'] < 1980):
			$d = [
				'year' => 1980,
				'mon' => 1,
				'mday' => 1,
				'hours' => 0,
				'minutes' => 0,
				'seconds' => 0
			];
		endif;
//		remove extra years from 1980
		$d['year'] -= 1980;
//		return date string
		return ($d['year'] << 25) | ($d['mon'] << 21) | ($d['mday'] << 16) | ($d['hours'] << 11) | ($d['minutes'] << 5) | ($d['seconds'] >> 1);
	}
/**
 * 	Create a format string and argument list for pack(), then call pack() and return the result.
 * 	@param array $fields
 * 	@return string
 */
	private function pack_fields($fields) {
		$fmt = '';
		$args = [];
//		populate format string and argument list
		foreach($fields as $field):
			$fmt .= $field[0];
			$args[] = $field[1];
		endforeach;
//		prepend format string to argument list
		array_unshift($args,$fmt);
//		build output string from header and compressed data
		return call_user_func_array('pack',$args);
	}
}
