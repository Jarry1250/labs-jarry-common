<?php

	/*
		Paul's Simple Diff Algorithm v 0.1
		(C) Paul Butler 2007 <http://www.paulbutler.org/>
		May be used and distributed under the zlib/libpng license.
		This code is intended for learning purposes; it was written with short
		code taking priority over performance. It could be used in a practical
		application, but there are a few ways it could be optimized.
		Given two arrays, the function diff will return an array of the changes.
		I won't describe the format of the array, but it will be obvious
		if you use print_r() on the result of a diff on some test data.
		htmlDiff is a wrapper for the diff command, it takes two strings and
		returns the differences in HTML. The tags used are <ins> and <del>,
		which can easily be styled with CSS.
	*/

	class Diff {
		private $html;

		function __construct( $before, $after ) {
			$before = preg_split( "/[\r\n]+/", $before );
			$after = preg_split( "/[\r\n]+/", $after );
			
			$diff = $this->diff( $before, $after );

			$this->html = '';
			foreach( $diff as $k ){
				if( is_array( $k ) ){
					$this->html .= ( !empty( $k['d'] ) ? "<del>" . implode( ' ', $k['d'] ) . "</del> " : '' ) .
					        ( !empty( $k['i'] ) ? "<ins>" . implode( ' ', $k['i'] ) . "</ins> " : '' );
				} else {
					$this->html .= $k . ' ';
				}
			}
		}
	
		private function diff ( $before, $after ) {
			$matrix = array();
			$maxlen = 0;
			foreach( $before as $oindex => $ovalue ){
				$nkeys = array_keys( $after, $ovalue );
				foreach( $nkeys as $nindex ){
					$matrix[$oindex][$nindex] = isset( $matrix[$oindex - 1][$nindex - 1] ) ?
						$matrix[$oindex - 1][$nindex - 1] + 1 : 1;
					if( $matrix[$oindex][$nindex] > $maxlen ){
						$maxlen = $matrix[$oindex][$nindex];
						$omax = $oindex + 1 - $maxlen;
						$nmax = $nindex + 1 - $maxlen;
					}
				}
			}
			if( $maxlen == 0 ){
				return array( array( 'd' => $before, 'i' => $after ) );
			}
			return array_merge(
				$this->diff( array_slice( $before, 0, $omax ), array_slice( $after, 0, $nmax ) ),
				array_slice( $after, $nmax, $maxlen ),
				$this->diff( array_slice( $before, $omax + $maxlen ), array_slice( $after, $nmax + $maxlen ) )
			);
		}

		function getDiff() {
			return $this->html;
		}

		function printDiff() {
			echo $this->html;
		}

		static function load( $before, $after ) {
			return new Diff( $before, $after );
		}
	}