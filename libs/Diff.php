<?php
	// Copyright (C) 2012 Jacob Barkdull
	//  and 2014 Harry Burt
	//
	//  This program is free software: you can redistribute it and/or modify
	//  it under the terms of the GNU Affero General Public License as
	//  published by the Free Software Foundation, either version 3 of the
	//  License, or (at your option) any later version.
	//
	//  This program is distributed in the hope that it will be useful,
	//  but WITHOUT ANY WARRANTY; without even the implied warranty of
	//  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	//  GNU Affero General Public License for more details.
	//
	//  You should have received a copy of the GNU Affero General Public License
	//  along with this program.  If not, see <http://www.gnu.org/licenses/>.

	class Diff {
		private $html;
		function __construct( $before, $after ) {
			// HTML Table
			$this->html = '<table width="100%">' . "\n<tbody>\n<tr>\n";
			$this->html .= '<td valign="top">' . "\nCurrent Version:<hr>\n<pre>\n";

			// Line Arrays
			$cv = explode( "\n", htmlspecialchars( $before ) ); // Current Version
			$ov = explode( "\n", htmlspecialchars( $after ) ); // Old Version

			// Count Lines - Set to Longer Version
			$lc = ( count( $cv ) > count( $ov ) ) ? count( $cv ) : count( $ov );

			// Fix Mismatched Line Counts
			for( $flc = count( $ov ); $flc != $lc; $flc++ ){
				$ov["$flc"] = '';
			}

			for( $l = '0'; $l != $lc; $l++ ){
				// Word Arrays
				$cw = explode( ' ', $cv["$l"] ); // Current Version
				$ow = explode( ' ', $ov["$l"] ); // Old Version

				// Count Words - Set to Longer Version
				$wc = ( count( $cw ) > count( $ow ) ) ? count( $cw ) : count( $ow );

				// Fix Mismatched Word Counts
				for( $fwc = count( $ow ); $fwc != $wc; $fwc++ ){
					$ow["$fwc"] = '';
				}

				// If each line is identical, just $this->html .= the normal line. If not,
				// check if each word is identical. If not, wrap colored "<b>"
				// tags around the mismatched words.
				if( $cv["$l"] !== $ov["$l"] ){
					for( $w = '0'; $w != $wc; $w++ ){
						if( $cw["$w"] === $ow["$w"] ){
							$this->html .= $cw["$w"];
							$this->html .= ( $w != ( $wc - 1 ) ) ? ' ' : "\n";
						} else {
							$this->html .= '<b style="color: #00BB00;">' . $cw["$w"];
							$this->html .= ( $w != ( $wc - 1 ) ) ? '</b> ' : "</b>\n";
						}
					}
				} else {
					$this->html .= $cv["$l"] . "\n";
				}
			}

			// Ending HTML Tags
			$this->html .= "</pre>\n</td>\n<td>&nbsp;</td>\n";
			$this->html .= '<td valign="top">' . "\nOld Version:<hr>\n<pre>\n";

			// Read and Display Old Version
			$this->html .= implode( "\n", $ov );
			$this->html .= "</pre>\n</td>\n</tr>\n</tbody>\n</table>";
		}

		function getDiff () {
			return $this->html;
		}

		function printDiff () {
			echo $this->html;
		}

		static function load( $before, $after ){
			return new Diff( $before, $after );
		}
	}