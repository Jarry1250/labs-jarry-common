<?php
	/*
	Database connection wrapper
	� 2013-14 Harry Burt <jarry1250@gmail.com>, plus various public domain
		code contributions

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

	function dbconnect( $database, $user = false ) {
		// connect using user credentials (local-toolname => /data/project/toolname/replica.my.cnf)
		$mycnf = parse_ini_file( "/data/project/" . substr( get_current_user(), 6 ) . "/replica.my.cnf" );
		$username = $mycnf['user'];
		$password = $mycnf['password'];
		unset( $mycnf );

		$cluster = ( preg_match( '/[-_]p$/', $database ) ) ? substr( $database, 0, -2 ) : $database;
		$db['connected'] = mysql_connect( $cluster . '.labsdb', $username, $password )
			|| print( '<p class="fail"><strong>Database server login failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon. '
				. ' The server returned: ' . mysql_error() . '</p>' );
		unset( $username );
		unset( $password );

		// select database
		$res = ( $db['connected'] ) ? mysql_select_db( str_replace( '-', '_', $database ) ) : false;

		return $res;
	}
