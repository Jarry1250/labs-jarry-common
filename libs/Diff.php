<?php
	/*
	Simple Diff class © 2014 Harry Burt <jarry1250@gmail.com>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	( at your option ) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	*/

	class Diff {
		private $new;
		private $old;
		private $unchanged = array();

		public function __construct( $old, $new ) {
			if( !is_array( $old ) ){
				$old = preg_split( '/[\r\n]+/', htmlspecialchars( $old ) );
			}
			if( !is_array( $new ) ){
				$new = preg_split( '/[\r\n]+/', htmlspecialchars( $new ) );
			}

			$this->new = $new;
			$this->old = $old;

			$newCount = count( $new );
			$oldCount = count( $old );
			$oldPointer = 0;
			$this->unchanged[] = array( -1, -1 );
			for( $i = 0; $i < $newCount; $i++ ){
				for( $j = $oldPointer; $j < min( $oldCount, $oldPointer + 10 ); $j++ ){
					if( $new[$i] === $old[$j] ){
						$this->unchanged[] = array( $i, $j );
						$oldPointer = $j + 1;
						break;
					}
				}
			}
			$this->unchanged[] = array( $newCount, $oldCount );
		}

		private function getChunks() {
			$chunks = array();
			$count = count( $this->unchanged );
			for( $i = 0; $i < ( $count - 1 ); $i++ ){
				$me = $this->unchanged[$i];
				$next = $this->unchanged[$i + 1];
				$removedRange = ( $me[1] + 1 == $next[1] ) ? array() : range( $me[1] + 1, $next[1] - 1 );
				$addedRange = ( $me[0] + 1 == $next[0] ) ? array() : range( $me[0] + 1, $next[0] - 1 );
				if( count( $removedRange ) === 0 && count( $addedRange ) === 0 ){
					continue;
				}
				$chunks[] = array(
					'before'  => $this->getOldLine( $me[1] ),
					'removed' => array_map( array( &$this, "getOldLine" ), $removedRange ),
					'added'   => array_map( array( &$this, "getNewLine" ), $addedRange ),
					'after'   => $this->getOldLine( $next[1] )
				);
			}
			return $chunks;
		}

		private function getOldLine( $i ) {
			if( $i == -1 ){
				return "[Beginning of file]";
			}
			if( $i == count( $this->old ) ){
				return "[End of file]";
			}
			return $this->old[$i];
		}

		private function getNewLine( $i ) {
			if( $i == -1 ){
				return "[Beginning of file]";
			}
			if( $i == count( $this->new ) ){
				return "[End of file]";
			}
			return $this->new[$i];
		}

		public function printUnifiedDiff( $context = true, $eol = "<br />" ) {
			$chunks = $this->getChunks();
			echo $eol;
			foreach( $chunks as $chunk ){
				if( $context ){
					echo $chunk['before'] . $eol;
				}
				foreach( $chunk['removed'] as $line ){
					echo "- " . $line . $eol;
				}
				foreach( $chunk['added'] as $line ){
					echo "+ " . $line . $eol;
				}
				if( $context ){
					echo $chunk['after'] . $eol;
				}
				echo $eol;
			}
		}

		static function load( $old, $new ) {
			return new Diff( $old, $new );
		}
	}