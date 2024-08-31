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

	echo "\n--- Beginning report ---\n";
	$wiki = Peachy::newWiki( 'livingbot' );
	$pageNames = [ "Wikipedia:Good articles/all", "Wikipedia:Good articles/all2" ];
	$subpages = [];

	foreach( $pageNames as $pageName ) {
		$page = new Page( $wiki, $pageName );
		$contents = $page->get_text();
		$bits = explode( "User:LivingBot", $contents, 2 );

		if( count( $bits ) !== 2 ) die( "Breakline not found on $pageName. Terminating.\n" );

		preg_match_all( "/\{\{Wikipedia:Good articles\/([^}]+)\}\}/i", $bits[1], $matches );
		$subpages = array_merge( $subpages, $matches[1] );
	}
	echo "Recognised " . count( $subpages ) . " subpages\n";

	$timestamps = array();
	foreach( $subpages as $subpage ){
		$page = new Page( $wiki, "Wikipedia:Good articles/$subpage" );
		$timestamp = floatval( str_replace( array( "-", ":", "T", "Z" ), "", $page->get_lastedit( true ) ) );
		array_push( $timestamps, $timestamp );
	}
	$mostRecentEditTimestamp = max( $timestamps );

	if( ( !isset( $_GET['nopost'] ) || $_GET['nopost'] != "y" ) && ( !isset( $_GET['ignoretime'] ) || $_GET['ignoretime'] != 'y' ) ){
		$previousCheck = time() - ( 16 * 60 ); // Slight overlapping is fine
		$previousCheckTimestamp = date( "YmdHis", $previousCheck );

		echo "Last check was at: $previousCheck (" . $previousCheckTimestamp . ")\n";
		echo "Last edit was at: " . $mostRecentEditTimestamp . "\n";
		if( floatval( $previousCheckTimestamp ) > $mostRecentEditTimestamp ){
			echo "No edits since last-but-one check.\n";
			echo "--- Finishing report ---\n";
			die();
		}
		echo "Edits since last-but-one check, proceeding...\n";
	}

	$existing = strtolower( file_get_contents( $filename ) );
	$articles = array();
	foreach( $subpages as $subpage ){
		$page = new Page( $wiki, "Wikipedia:Good articles/$subpage" );
		$contents = $page->get_text();
		preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $temp );
		echo count( $temp[0] ) . "-";
		$articles = array_merge( $articles, $temp[0] );
	}
	$total = strtolower( implode( "\t", $articles ) );

	$page = new Page( $wiki, "Wikipedia talk:Good articles" );
	$contents = $page->get_text();
	preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $underDiscussion );

	$page = new Page( $wiki, "Wikipedia:Good articles/recent" );
	$contents = $page->get_text();
	preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", $contents, $recents );
	$recentlist = $recents[0];

	if( count( $articles ) == count( explode( "\t", $existing ) ) ){
		file_put_contents( $filename, $total );
		echo "Overall, the number of GAs has remained the same. Assuming only renames, or similar.\n";
		echo "--- Finishing report ---\n";
		die();
	}

	if( count( $articles ) < ( count( explode( "\t", $existing ) ) ) ){
		file_put_contents( $filename, $total );
		echo "Overall, the number of GAs has gone down. Assumed malfunction.\n";
		echo "--- Finishing report ---\n";
		die();
	}

	$addeds = array();
	$removeds = array();
	echo "Presently listed items:\n* " . implode( $recentlist, "\n* " ) . "\n";
	foreach( $articles as $article ){
		$delimiter = ( strpos( $article, "|" ) > 0 ) ? '|' : ']';
		$subitem = substr( $article, strpos( $article, "[" ), ( strpos( $article, $delimiter ) - strpos( $article, "[" ) ) );
		$subitem = strtolower( $subitem );
		if( strpos( $existing, $subitem ) !== false ){
			// Already known to the bot
			continue;
		}
		if( strpos( strtolower( implode( "", $recentlist ) ), $subitem ) !== false ){
			// Already listed on /recent: presumably manually added
			continue;
		}
		if( strpos( strtolower( implode( "", $underDiscussion[0] ) ), $subitem ) !== false ){
			// A link target of the WT:GA page: under discussion/controversial
			continue;
		}
		echo "Item added: $article\n";
		$addeds[] = $article;

		// Insert new item into page text
		$pos = strpos( $contents, "-->" ) + 3;
		$contents = substr( $contents, 0, $pos ) . "\n$article &mdash;" . substr( $contents, $pos );

		// Record and remove final item from page text
		$pos = strrpos( $contents, "&mdash" );
		preg_match_all( "/('')?\[\[([^]:]*)\]\]('')?/i", substr( $contents, $pos ), $removed );
		$removeds = array_merge( $removeds, $removed[0] );
		$contents = substr( $contents, 0, $pos ) . "\n\n<!--\nAdd new articles to the top of the list, please, and remove the one from the bottom INCLUDING THE EM-DASH ON THE LINE BEFORE. Thanks! \n-->";

	}
	if( count( $addeds ) > 0 && ( count( $addeds ) < 6 || isset( $_GET['ignoretime'] ) ) && !isset( $_GET['nopost'] ) ){
		$page = new Page( $wiki, "Wikipedia:Good articles/recent" );

		$addedStr = ( count( $addeds ) > 1 ) ? 's (' : ' (';
		$addedStr .= implode( ', ', $addeds ) . ')';

		$removedStr = ( count( $addeds ) > 1 ) ? 'ies (' : 'y (';
		$removedStr .= implode( ', ', $removeds ) . ')';

		$editSummary = "Bot adding recently promoted article$addedStr, removing oldest entr$removedStr. [[User_talk:LivingBot|Incorrect?]]";

		$i = 0;
		while( ++$i < 5 ){
			if( $page->edit( $contents, substr( $editSummary, 0, 250 ), false, true ) !== false ) break;
			sleep( 5 );
		}
		echo ( $i == 5 ) ? "Tried but failed to write changes." : "Changes written.";
	} else {
		echo "Changes not written (edited=" . count( $addeds ) . ")";
	}
	file_put_contents( $filename, $total );
	echo "\n--- Finishing report ---\n";
