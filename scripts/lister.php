<?php
	/*
	Recent good articles lister © 2011 and 2013 Harry Burt <jarry1250@gmail.com>

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; either version 2 of the License, or
	(at your option) any later version.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	*/

	require_once( '/data/project/jarry-common/public_html/peachy/Init.php' );
	$filename = '/data/project/jarry-common/public_html/scripts/ga.txt';

	global $wiki;
	$wiki = Peachy::newWiki( 'livingbot' );

	$page = new Page( $wiki, "Wikipedia:Good articles/all" );
	$contents = $page->get_text();
	$bits = explode( "<!-- Do not remove this line, LivingBot", $contents, 2 );

	if( count( $bits ) !== 2 ) die( "Breakline not found. Terminating.\n" );

	preg_match_all( "/\{\{Wikipedia:Good articles\/([^}]+)\}\}/i", $bits[1], $subpages );
	$subpages = $subpages[1];
	echo "Recognised the following subpages: ";
	print_r( $subpages );

	$timestamps = array();
	foreach( $subpages as $subpage ){
		$page = new Page( $wiki, "Wikipedia:Good articles/$subpage" );
		$timestamp = floatval( str_replace( array( "-", ":", "T", "Z" ), "", $page->get_lastedit( true ) ) );
		array_push( $timestamps, $timestamp );
	}
	$timestamp = max( $timestamps );

	echo "--- Beginning report ---\n";
	if( ( !isset( $_GET['nopost'] ) || $_GET['nopost'] != "y" ) && ( !isset( $_GET['ignoretime'] ) || $_GET['ignoretime'] != 'y' ) ){
		$backfive = time() - 700;
		$nowstring = floatval( date( "YmdHis", $backfive ) );

		echo "Last check was at: " . $nowstring . "\n";
		echo "Last edit was at: " . $timestamp . "\n";
		if( $nowstring > $timestamp ){
			echo "No edits since last-but-one check.\n";
			echo "--- Finishing report ---\n";
			die();
		}
	}

	$old = strtolower( file_get_contents( $filename ) );
	$matches = array();
	foreach( $subpages as $subpage ){
		$page = new Page( $wiki, "Wikipedia:Good articles/$subpage" );
		$contents = $page->get_text();
		preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $temp );
		echo count( $temp[0] ) . "-";
		$matches = array_merge( $matches, $temp[0] );
	}
	$page = new Page( $wiki, "Wikipedia talk:Good articles" );
	$contents = $page->get_text();
	preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $listed );
	$page = new Page( $wiki, "Wikipedia:Good articles/recent" );
	$contents = $page->get_text();
	preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $recents );
	$recentlist = $recents[0];
	$total = strtolower( implode( "\t", $matches ) );
	if( count( $matches ) == ( count( explode( "\t", $old ) ) ) ){
		file_put_contents( $filename, $total );
		echo "Overall, the number of GAs has remained the same. Assuming only renames, or similar.\n";
		echo "--- Finishing report ---\n";
		die();
	}
	if( count( $matches ) < ( count( explode( "\t", $old ) ) ) ){
		file_put_contents( $filename, $total );
		echo "Overall, the number of GAs has gone down. Assumed malfunction.\n";
		echo "--- Finishing report ---\n";
		die();
	}
	$edited = 0;
	$items = " (";
	$plural = "y (";
	$removeds[] = array();
	echo "Presently listed items:\n* " . implode( $recentlist, "\n* " ) . "\n";
	for( $i = 0; $i < ( count( $matches ) - 1 ); $i++ ){
		$item = trim( $matches[$i] );
		$delimiter = ( strpos( $item, "|" ) > 0 ) ? '|' : ']';
		$subitem = strtolower( substr( $item, strpos( $item, "[" ), ( strpos( $item, $delimiter ) - strpos( $item, "[" ) ) ) );
		if( strpos( $old, $subitem ) !== false ){
			continue;
		}
		if( strpos( strtolower( implode( "", $recentlist ) ), $subitem ) !== false ){
			continue;
		}
		if( strpos( strtolower( implode( "", $listed[0] ) ), $subitem ) !== false ){
			continue;
		}
		echo "Item added: $item\n";
		if( $edited > 0 ){
			$items .= ", ";
		}
		if( $edited == 1 ){
			$items = "s" . $items;
			$plural = "ies (";
		}
		$items .= $item;
		$edited++;
		$pos = strpos( $contents, "[" );
		if( substr( substr( $contents, 0, $pos ), -1 ) == "'" ){
			$pos -= 2;
		}
		$contents = substr( $contents, 0, $pos ) . "$item &mdash;\n" . substr( $contents, $pos );
		$pos = strlen( $contents ) - strpos( strrev( $contents ), "hsadm&" ) - 7;
		preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", substr( $contents, $pos ), $removed );
		for( $a = 0; $a < count( $removed[0] ); $a++ ){
			array_push( $removeds, $removed[0][$a] );
		}
		$contents = substr( $contents, 0, $pos ) . "\n\n<!--\nAdd new articles to the top of the list, please, and remove the one from the bottom INCLUDING THE EM-DASH ON THE LINE BEFORE. Thanks! \n-->";

	}
	array_shift( $removeds );
	$removed = $plural . implode( $removeds, ", " ) . ")";
	$items .= ")";
	if( $edited > 0 && ( $edited < 3 || isset( $_GET['ignoretime'] ) ) && !isset( $_GET['nopost'] ) ){
		$page = new Page( $wiki, "Wikipedia:Good articles/recent" );
		$i = 0;
		while( ++$i < 5 ){
			if( $page->edit( $contents, "Bot adding recently promoted article$items, removing oldest entr$removed. [[User_talk:LivingBot|Incorrect?]]", false, true ) !== false ){
				file_put_contents( $filename, $total );
				break;
			}
			sleep( 5 );
		}
		if( $i == 5 ) {
			file_put_contents( $filename, $total );
			echo "Tried but failed to write changes.\n";
		} else {
			echo "Changes written.\n";
		}
	} else {
		file_put_contents( $filename, $total );
		echo "Changes not written (edited=$edited).\n";
	}

	echo "--- Finishing report ---\n";