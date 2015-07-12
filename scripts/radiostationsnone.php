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
	$arzeroth = array();
	$arfirst = array();
	$arsecond = array();
	$arthird = array();
	$arfourth = array();
	$arfifth = array();
	$arsixth = array();
	$arseventh = array();
	$areighth = array();
	$arninth = array();

	function getSql( $tlTitle ) {
		return "SELECT page_title FROM page INNER JOIN templatelinks ON page_namespace=0 AND page_is_redirect=0 AND page_id = tl_from AND tl_title='" . htmlspecialchars( $tlTitle ) . "' AND tl_namespace=10";
	}
	$zeroth = $database->query( getSql( 'Infobox_Radio_Station' ) );
	$first = $database->query( getSql( 'AMQ' ) );
	$second = $database->query( getSql( 'AML' ) );
	$third = $database->query( getSql( 'AMARB' ) );
	$fourth = $database->query( getSql( 'FMQ' ) );
	$fifth = $database->query( getSql( 'FML' ) );
	$sixth = $database->query( getSql( 'FMARB' ) );
	$seventh = $database->query( getSql( 'LPL' ) );
	$eighth = $database->query( getSql( 'AM_station_data' ) );
	$ninth = $database->query( getSql( 'FM_station_data' ) );

	while( $row = $zeroth->fetch_row() ){
		array_push( $arzeroth, $row[0] );
	}
	while( $row = $first->fetch_row() ){
		array_push( $arfirst, $row[0] );
	}
	while( $row = $second->fetch_row() ){
		array_push( $arsecond, $row[0] );
	}
	while( $row = $third->fetch_row() ){
		array_push( $arthird, $row[0] );
	}
	while( $row = $fourth->fetch_row() ){
		array_push( $arfourth, $row[0] );
	}
	while( $row = $fifth->fetch_row() ){
		array_push( $arfifth, $row[0] );
	}
	while( $row = $sixth->fetch_row() ){
		array_push( $arsixth, $row[0] );
	}
	while( $row = $seventh->fetch_row() ){
		array_push( $arseventh, $row[0] );
	}
	while( $row = $eighth->fetch_row() ){
		array_push( $areighth, $row[0] );
	}
	while( $row = $ninth->fetch_row() ){
		array_push( $arninth, $row[0] );
	}

	$problems = array();
	for( $i = 0; $i < count( $arzeroth ); $i++ ){
		$page = $arzeroth[$i];
		if( in_array( $page, $arfirst ) ){
			continue;
		}
		if( in_array( $page, $arsecond ) ){
			continue;
		}
		if( in_array( $page, $arthird ) ){
			continue;
		}
		if( in_array( $page, $arfourth ) ){
			continue;
		}
		if( in_array( $page, $arfifth ) ){
			continue;
		}
		if( in_array( $page, $arsixth ) ){
			continue;
		}
		if( in_array( $page, $arseventh ) ){
			continue;
		}
		if( in_array( $page, $areighth ) ){
			continue;
		}
		if( in_array( $page, $arninth ) ){
			continue;
		}
		array_push( $problems, $page );
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