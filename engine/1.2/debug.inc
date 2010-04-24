<?php

# helper function.. calls print_a() inside the print_a_class
function print_a( $array, $mode = 0, $show_object_vars = FALSE, $limit = FALSE ) {
	$output = '';

	if( is_array( $array ) or is_object( $array ) ) {
		$pa = new Print_a_class;
		$show_object_vars and $pa->show_object_vars = TRUE;
		if( $limit ) {
			$pa->limit = $limit;
			// $output .= '<span style="color:red;">showing only '.$limit.' entries for arrays with numeric keys</span>';
		}

		if ( is_object($array) ) {
	      $pa->print_a( get_object_vars($array) );
		} else {
	      $pa->print_a( $array );
		}

		# $output = $pa->output; unset($pa);
		$output .= $pa->output;
	} else {
		$output .= '<span style="color:red;font-size:small;">print_a( '.gettype( $array ).' )</span>';
	}

	if($mode == 0) {
		print $output;
		return TRUE;
	}

	if($mode == 1) {
		return $output;
	}


	if($mode == 2) {
		$debugwindow_origin = $_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];
		print '
			<script type="text/javascript" language="JavaScript">
				var debugwindow;
				debugwindow = window.open("", "T_'.md5($_SERVER['HTTP_HOST']).'", "menubar=no,scrollbars=yes,resizable=yes,width=640,height=480");
				debugwindow.document.open();
				debugwindow.document.write("'.addslashes($output).'");
				debugwindow.document.close();
				debugwindow.document.title = "Debugwindow for : http://'.$debugwindow_origin.'";
				debugwindow.focus();
			</script>
		';
	}

	if($mode == 3) {
		print '
			<script type="text/javascript" language="JavaScript">
				var debugwindow;
				debugwindow = window.open("", "S_'.md5($_SERVER['HTTP_HOST']).'", "menubar=yes,scrollbars=yes,resizable=yes,width=640,height=480");
				debugwindow.document.open();
				debugwindow.document.write("'.addslashes( serialize($array) ).'");
				debugwindow.document.close();
				debugwindow.document.title = "Debugwindow for : http://'.$debugwindow_origin.'";
				debugwindow.focus();
			</script>
		';
	}

}

class Print_a_class {

	# this can be changed to TRUE if you want
	var $look_for_leading_tabs = FALSE;

	var $output;
	var $iterations;
	var $key_bg_color = '1E32C8';
	var $value_bg_color = 'DDDDEE';
	var $fontsize = '8pt';
	var $keyalign = 'center';
	var $fontfamily = 'Verdana';
	var $show_object_vars;
	var $limit;

	// function Print_a_class() {}

	# recursive function!

	/* this internal function looks if the given array has only numeric values as  */
	function _only_numeric_keys( $array ) {
		$test = TRUE;
		if (is_array($array)) {
			foreach( array_keys( $array ) as $key ) {
				if( !is_numeric( $key ) )	$test = FALSE;
			}
			return $test;
		} else {
			return FALSE;
		}

	}

	function _handle_whitespace( $string ) {
		$string = str_replace(' ', '&nbsp;', $string);
		$string = preg_replace(array('/&nbsp;$/', '/^&nbsp;/'), '<span style="color:red;">_</span>', $string); /* replace spaces at the start/end of the STRING with red underscores */
		$string = preg_replace('/\t/', '&nbsp;&nbsp;<span style="border-bottom:#'.$this->value_bg_color.' solid 1px;">&nbsp;</span>', $string); /* replace tabulators with '_ _' */
		return $string;
	}

	function print_a($array, $iteration = FALSE, $key_bg_color = FALSE) {
		$key_bg_color or $key_bg_color = $this->key_bg_color;

		# lighten up the background color for the key td's =)
		if( $iteration ) {
			for($i=0; $i<6; $i+=2) {
				$c = substr( $key_bg_color, $i, 2 );
				$c = hexdec( $c );
				( $c += 15 ) > 255 and $c = 255;
				isset($tmp_key_bg_color) or $tmp_key_bg_color = '';
				$tmp_key_bg_color .= sprintf( "%02X", $c );
			}
			$key_bg_color = $tmp_key_bg_color;
		}

		# build a single table ... may be nested
		$this->output .= '<table style="border:none;" cellspacing="1">';
		$only_numeric_keys = ($this->_only_numeric_keys( $array ) || count( $array ) > 50);
		$i = 0;
		foreach( $array as $key => $value ) {

			if( $only_numeric_keys && $this->limit && $this->limit == $i++ ) break; /* if print_a() was called with a fourth parameter */

			$value_style_box = 'color:black;';
			$key_style = 'color:white;';

			$type = gettype( $value );
			# print $type.'<br />';

			# change the color and format of the value and set the values title
			$type_title = $type;
			$value_style_content = '';
			switch( $type ) {
				case 'array':
					if( empty( $value ) ) $type_title = 'empty array';
					break;

				case 'object':
					$key_style = 'color:#FF9B2F;';
					break;

				case 'integer':
					$value_style_box = 'color:green;';
					break;

				case 'double':
					$value_style_box = 'color:blue;';
					break;

				case 'boolean':
					$value_style_box = 'color:#D90081;';
					break;

				case 'NULL':
					$value_style_box = 'color:darkorange;';
					break;

				case 'string':
					if( $value == '' ) {

						$value_style_box = 'color:darkorange;';
						$value = "''";
						$type_title = 'empty string';

					} else {

						$value_style_box = 'color:black;';
						$value = htmlspecialchars( $value );
						if( $this->look_for_leading_tabs && _check_for_leading_tabs( $value ) ) {
							$value = _remove_exessive_leading_tabs( $value );
						}
						$value = $this->_handle_whitespace( $value );
						$value = nl2br($value);

						/* use different color for string background */
						if(strstr($value, "\n")) $value_style_content = 'background:#ECEDFE;';

					}
					break;
			}

			$this->output .= '<tr>';
			$this->output .= '<td nowrap="nowrap" align="'.$this->keyalign.'" style="padding:0px 3px 0px 3px;background-color:#'.$key_bg_color.';'.$key_style.';font:bold '.$this->fontsize.' '.$this->fontfamily.';" title="'.gettype( $key ).'['.$type_title.']">';
			$this->output .= $this->_handle_whitespace( $key );
			$this->output .= '</td>';
			$this->output .= '<td nowrap="nowrap" style="background-color:#'.$this->value_bg_color.';font: '.$this->fontsize.' '.$this->fontfamily.'; color:black;">';


			# value output
			if($type == 'array' && preg_match('/#RAS/', $key) ) { /* only used for special recursive array constructs which i use sometimes */
				$this->output .= '<div style="'.$value_style_box.'">recursion!</div>';
			} elseif($type == 'array') {
				if( ! empty( $value ) ) {
					$this->print_a( $value, TRUE, $key_bg_color );
				} else {
					$this->output .= '<span style="color:darkorange;">[]</span>';
				}
			} elseif($type == 'object') {
				if( $this->show_object_vars ) {
					$this->print_a( get_object_vars( $value ), TRUE, $key_bg_color );
				} else {
					$this->output .= '<div style="'.$value_style_box.'">OBJECT</div>';
				}
			} elseif($type == 'boolean') {
				$this->output .= '<div style="'.$value_style_box.'" title="'.$type.'">'.($value ? 'TRUE' : 'FALSE').'</div>';
			} elseif($type == 'NULL') {
				$this->output .= '<div style="'.$value_style_box.'" title="'.$type.'">NULL</div>';
			} else {
				$this->output .= '<div style="'.$value_style_box.'" title="'.$type.'"><span style="'.$value_style_content.'">'.$value.'</span></div>';
			}

			$this->output .= '</td>';
			$this->output .= '</tr>';
		}

		$entry_count = count( $array );
		$skipped_count = $entry_count - $this->limit;

		if( $only_numeric_keys && $this->limit && count($array) > $this->limit) {
			$this->output .= '<tr title="showing '.$this->limit.' of '.$entry_count.' entries in this array"><td style="text-align:right;color:darkgray;background-color:#'.$key_bg_color.';font:bold '.$this->fontsize.' '.$this->fontfamily.';">...</td><td style="background-color:#'.$this->value_bg_color.';font:'.$this->fontsize.' '.$this->fontfamily.';color:darkgray;">['.$skipped_count.' skipped]</td></tr>';
		}
		$this->output .= '</table>';
	}
}

function encode_good( $good_id, $salt ) {

	$str2enc = $salt . '#' . $good_id . '#' . $salt;

	/* Open the cipher */
	$td = mcrypt_module_open ('rijndael-256', '', 'ofb', '');

	/* Create the IV and determine the keysize length */
	$iv = hex2bin('7be7f1978bf5fa5590122df30285052f5da7f99f5fa9e35d9284f3e1bb450bbf');
	$ks = mcrypt_enc_get_key_size ($td);

	/* Create key */
	$secret_key = substr ('72c8f24204ab8b574e63cd9b2deffceb', 0, $ks);

	/* Intialize encryption */
	mcrypt_generic_init ($td, $secret_key, $iv);

	/* Encrypt data */
	$encrypted = rawurlencode(base64_encode( mcrypt_generic ($td, $str2enc) ));

	/* Terminate encryption handler */
	mcrypt_generic_deinit ($td);
	mcrypt_module_close ($td);

	return $encrypted;

}

function decode_good( $enc_str ) {

	/* Open the cipher */
	$td = mcrypt_module_open ('rijndael-256', '', 'ofb', '');

	/* Create the IV and determine the keysize length */
	$iv = hex2bin('7be7f1978bf5fa5590122df30285052f5da7f99f5fa9e35d9284f3e1bb450bbf');
	$ks = mcrypt_enc_get_key_size ($td);

	/* Create key */
	$secret_key = substr ('72c8f24204ab8b574e63cd9b2deffceb', 0, $ks);

	/* Initialize encryption module for decryption */
	mcrypt_generic_init ($td, $secret_key, $iv);

	/* Decrypt encrypted string */
	$decrypted = mdecrypt_generic ($td, base64_decode( rawurldecode($enc_str) ));

	/* Terminate decryption handle and close module */
	mcrypt_generic_deinit ($td);
	mcrypt_module_close ($td);

	$first = strpos($decrypted, '#');
	$second = strpos($decrypted, '#', $first + 1);

	$good_id = substr( $decrypted, $first + 1, $second - $first - 1);

	return $good_id;

}

?>