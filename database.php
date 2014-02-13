<?php
	/*
	Database connection wrapper
	� 2013 Harry Burt <jarry1250@gmail.com>, plus various public domain
		code contributions

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

	function dbconnect( $database, $user = false ) {
		// connect
		$mycnf = parse_ini_file( "/data/project/" . get_current_user() . "/replica.my.cnf" );
		die( "/data/project/" . get_current_user() . "/replica.my.cnf" );
		$username = $mycnf['user'];
		$password = $mycnf['password'];
		unset( $mycnf );
		$db['connected'] = mysql_connect( $database . '.labsdb', $username, $password )
			|| print( '<p class="fail"><strong>Database server login failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon. '
				. ' The server returned: ' . mysql_error() . '</p>' );
	unset( $username );
	unset( $password );

	// select database
	$res = ( $db['connected'] ) ? mysql_select_db( str_replace( '-', '_', $database ) ) : false;

	// restore error-reporting
	return $res;
}

?>
