<?php
	/**
	 * radiostations.php (c)2011-15
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

	function getSql( $tlTitle ) {
		return "SELECT page_title FROM page INNER JOIN templatelinks ON page_namespace=0 AND page_is_redirect=0 AND page_id = tl_from AND tl_title='" . htmlspecialchars( $tlTitle ) . "' AND tl_namespace=10";
	}

	// connect to database
	require_once( '/data/project/jarry-common/public_html/libs/database.php' );
	$database = dbconnect( "enwiki-p" );
	$arfirst = array();
	$arsecond = array();
	$arthird = array();
	$arfourth = array();
	$arfifth = array();
	$arsixth = array();
	$arseventh = array();

	$first = $database->query( getSql( 'AMQ' ) );
	$second = $database->query( getSql( 'AML' ) );
	$third = $database->query( getSql( 'AMARB' ) );
	$fourth = $database->query( getSql( 'FMQ' ) );
	$fifth = $database->query( getSql( 'FML' ) );
	$sixth = $database->query( getSql( 'FMARB' ) );
	$seventh = $database->query( getSql( 'LPL' ) );

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
	$problems = array();
	$problems = array_merge( $problems, array_diff( $arfirst, $arsecond ) );
	$problems = array_merge( $problems, array_diff( $arfirst, $arthird ) );
	$problems = array_merge( $problems, array_diff( $arsecond, $arfirst ) );
	$problems = array_merge( $problems, array_diff( $arsecond, $arthird ) );
	$problems = array_merge( $problems, array_diff( $arthird, $arfirst ) );
	$problems = array_merge( $problems, array_diff( $arthird, $arsecond ) );

	$problems = array_merge( $problems, array_diff( $arfourth, $arsixth ) );
	$problems = array_merge( $problems, array_diff( $arfifth, $arfourth ) );
	$problems = array_merge( $problems, array_diff( $arfifth, $arsixth ) );
	$problems = array_merge( $problems, array_diff( $arsixth, $arfourth ) );

	$problems = array_merge( $problems, array_intersect( array_diff( $arfourth, $arfifth ), array_diff( $arfourth, $arseventh ) ) );
	$problems = array_merge( $problems, array_intersect( array_diff( $arsixth, $arfifth ), array_diff( $arsixth, $arseventh ) ) );

	$problems = array_unique( $problems );
	sort( $problems );
	$count = count( $problems );

	echo get_html( 'header', 'Radio stations' );
	echo '<p>This is a list of pages about radio stations which use one or more of three templates but not all three.</p>';
	echo "<h3>Here's the list ($count listed):</h3>\n<ul>\n";
	for( $i = 0; $i < count( $problems ); $i++ ){
		$nt = $problems[$i];
		echo "<li><a href=\"https://en.wikipedia.org/wiki/$nt\">$nt</a> (";
		echo "<a href=\"https://en.wikipedia.org/wiki/Talk:$nt\">talk</a> | ";
		echo "<a href=\"https://en.wikipedia.org/w/index.php?title=$nt&action=edit\">edit</a>)</li>\n";
	}
	echo "</ul>\n";
	echo get_html( 'footer' );
