<?php
	/**
	 * radiostationsnone.php (c)2011-15
	 * @author Harry Burt <jarry1250@gmail.com>
	 *
	 * This program is free software; you can redistribute it and/or modify
	 * it under the terms of the GNU General Public License as published by
	 * the Free Software Foundation; either version 2 of the License, or
	 * (at your option) any later version.
	 *
	 * This program is distributed in the hope that it will be useful,
	 * but WITHOUT ANY WARRANTY; without even the implied warranty of
	 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
	 * GNU General Public License for more details.
	 *
	 * You should have received a copy of the GNU General Public License
	 * along with This program; if not, write to the Free Software
	 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
	 */

	require_once( '/data/project/jarry-common/public_html/global.php' );

	// connect to database
	require_once( '/data/project/jarry-common/public_html/libs/database.php' );

	$database = dbconnect( "enwiki-p" );

	$allWithInfobox = array();
	$allWithInfoboxQuery = $database->query( "SELECT page_title FROM page INNER JOIN templatelinks ON page_namespace=0 AND page_is_redirect=0 AND page_id = tl_from AND tl_title='Infobox_Radio_Station' AND tl_namespace=10" );
	while( $row = $allWithInfoboxQuery->fetch_row() ){
		array_push( $allWithInfobox, $row[0] );
	}

	$haveTemplates = array();
	$haveTemplatesQuery = $database->query("SELECT page_title FROM page INNER JOIN templatelinks ON page_namespace=0 AND page_is_redirect=0 AND page_id = tl_from AND ( tl_title='AMQ' OR tl_title='AML' OR tl_title='AMARB' OR tl_title='FMQ' OR tl_title='FML' OR tl_title='FMARB' OR tl_title='LPL' OR tl_title='AM_station_data' OR tl_title='FM_station_data' ) AND tl_namespace=10" );
	while( $row = $haveTemplatesQuery->fetch_row() ){
		array_push( $haveTemplates, $row[0] );
	}

	$problems = array();
	foreach( $allWithInfobox as $page ) {
		if( !in_array( $page, $haveTemplates ) ){
			array_push( $problems, $page );
		}
	}

	sort( $problems );
	$count = count( $problems );
	echo get_html( 'header', 'Radio stations (2)' );
	echo '<p>This is a list of pages about radio stations which don\'t use any useful information templates.</p>';
	echo "<h3>Here's the list ($count listed):</h3>\n<ul>\n";
	for( $i = 0; $i < count( $problems ); $i++ ){
		$nt = $problems[$i];
		echo "<li><a href=\"http://en.wikipedia.org/wiki/$nt\">$nt</a> (";
		echo "<a href=\"http://en.wikipedia.org/wiki/Talk:$nt\">talk</a> | ";
		echo "<a href=\"http://en.wikipedia.org/w/index.php?title=$nt&action=edit\">edit</a>)</li>\n";
	}
	echo "</ul>\n";
	echo get_html( 'footer' );