<?php
	/**
	 * callsigns.php (c)2011-15
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
	$tagged = array();
	$result = $database->query( "select page_title from page inner join categorylinks c1 on cl_from = page_id and cl_to='Disambiguation_pages' left join categorylinks c2 on c2.cl_from = c1.cl_from and c2.cl_to='Broadcast_call_sign_disambiguation_pages' where c2.cl_from is null and (page_title like 'C%' or page_title like 'K%' or page_title like 'W%')" );
	while( $row = $result->fetch_assoc() ){
		$page = str_replace( "_", " ", $row["page_title"] );
		$pos = stripos( $page, " (disambiguation)" );
		if( $pos != 0 ){
			$page = substr( $page, 0, $pos );
		}
		if( check( $page ) ){
			array_push( $tagged, $page );
		}
	}

	function check( $page ) {
		if( strtoupper( $page ) !== $page ){
			return false;
		}
		if( strlen( $page ) !== 3 && strlen( $page ) !== 4 ){
			return false;
		}
		return preg_match( "/^[a-zA-Z]+$/", $page );
	}
	sort( $tagged );
	$count = count( $tagged );

	echo get_html( 'header', 'Potential Callsign Disambiguation Pages' );
	echo '<p>This is a list of disambiguation pages on en Wikipedia which follow a set list of rules.</p>';
	echo "<h3>Here's the list:</h3>\n<ul>\n";
	for( $i = 0; $i < count( $tagged ); $i++ ){
		$nt = $tagged[$i];
		echo "<li><a href=\"http://en.wikipedia.org/wiki/$nt\">$nt</a> (";
		echo "<a href=\"http://en.wikipedia.org/wiki/Talk:$nt\">talk</a> | ";
		echo "<a href=\"http://en.wikipedia.org/w/index.php?title=$nt&action=edit\">edit</a>)</li>\n";
	}
	echo "</ul>\n";
	echo get_html( 'footer' );