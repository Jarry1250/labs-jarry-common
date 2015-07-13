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

	$pages = array();
	$allWithInfoboxQuery = $database->query(
		"SELECT page_title FROM page
			INNER JOIN templatelinks AS t1 ON page_namespace=0 AND page_is_redirect=0
				AND page_id=t1.tl_from AND t1.tl_title='Infobox_radio_station' AND t1.tl_namespace=10
			LEFT JOIN templatelinks AS t2 ON page_id=t2.tl_from AND t2.tl_namespace=10
				AND ( t2.tl_title='AMQ' OR t2.tl_title='AML' OR t2.tl_title='AMARB' OR t2.tl_title='FMQ'
					OR t2.tl_title='FML' OR t2.tl_title='FMARB' OR t2.tl_title='LPL' OR t2.tl_title='AM_station_data'
					OR t2.tl_title='FM_station_data' OR t2.tl_title='LPFM_station_data' )
		 WHERE t2.tl_namespace IS NULL;"
	);
	while( $row = $allWithInfoboxQuery->fetch_row() ){
		array_push( $pages, $row[0] );
	}

	sort( $pages );
	$count = count( $pages );
	echo get_html( 'header', 'Radio stations (2)' );
	echo '<p>This is a list of pages about radio stations which don\'t use any useful information templates.</p>';
	echo "<h3>Here's the list ($count listed):</h3>\n<ul>\n";
	for( $i = 0; $i < count( $pages ); $i++ ){
		$nt = $pages[$i];
		echo "<li><a href=\"http://en.wikipedia.org/wiki/$nt\">$nt</a> (";
		echo "<a href=\"http://en.wikipedia.org/wiki/Talk:$nt\">talk</a> | ";
		echo "<a href=\"http://en.wikipedia.org/w/index.php?title=$nt&action=edit\">edit</a>)</li>\n";
	}
	echo "</ul>\n";
	echo get_html( 'footer' );
