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

		$cluster = ( preg_match( '/[-_]p$/', $database ) ) ? substr( $database, 0, -2 ) : $database;
		$mysqli = new mysqli( $cluster . '.labsdb', $mycnf['user'], $mycnf['password'] );
		unset( $mycnf );

		if( $mysqli->connect_error ) {
			die( '<p class="fail"><strong>Database server login failed.</strong> '
				. ' This is probably a temporary problem with the server and will be fixed soon. '
				. ' The server returned error code ' . $mysqli->connect_errno . '.</p>' );
		}

		// select database
		$res = $mysqli->select_db( str_replace( '-', '_', $database ) );

		if( $res === false ){
			die( '<p class="fail"><strong>Database selection failed.</strong> '
				 . ' This is probably a temporary problem with the server and will be fixed soon.' );
		}

		return $mysqli;
	}
