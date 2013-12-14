<?php
/*
Database connection wrapper
© 2013 Harry Burt <jarry1250@gmail.com>, plus various public domain
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

function dbconnect($database, $user = false) {
	// fix redundant error-reporting
	$errorlevel = ini_set('error_reporting','0');

	// connect
	$mycnf = parse_ini_file("/home/".get_current_user()."/.my.cnf");
	$username = $mycnf['user'];
	$password = $mycnf['password'];
	unset($mycnf);
	$prefix = $user ? "sql-user-j" : $database . '.rrdb';
	$db['connected'] = mysql_connect($prefix . '.toolserver.org',$username,$password) 
			or print '<p class="fail"><strong>Database server login failed.</strong> '
					 . ' This is probably a temporary problem with the server and will be fixed soon. '
					 . ' The server returned: ' . mysql_error() . '</p>';
	unset($username);
	unset($password);

	// select database
	if($db['connected']) {
		$res = mysql_select_db(str_replace('-','_',$database));
	}

	// restore error-reporting
	ini_set('error-reporting',$errorlevel);    
	return $res;
}
?>
