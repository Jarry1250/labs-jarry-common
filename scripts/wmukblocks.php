<?php
	/*
	WMUK block copier © 2011 and 2013 Harry Burt <jarry1250@gmail.com>

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

	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );

	require_once( '/data/project/jarry-common/public_html/peachy/Init.php' );
	$wiki = Peachy::newWiki( 'wmuk' );
	$http = new HTTP();

	// For efficiency, only request newish blocks (last check + some overlap)
	$lastBlock = $wiki->logs( false, 'Global block importer bot', false, false, false, 'older', false, array( 'timestamp' ), 1 );
	$lastBlockAdjusted = ( count( $lastBlock ) > 0 )
		? date( 'Y-m-d\TH:i:s\Z', ( strtotime( $lastBlock[0]['timestamp'] ) - 900 ) )
		: date( 'Y-m-d\TH:i:s\Z', 1000000000 );

	$meta = Peachy::newWiki( 'livingbotmeta' );
	$query = array(
		'_code' => 'bg',
		'_limit' => 10000,
		'action' => 'query',
		'list' => 'globalblocks',
		'bgprop' => 'address|expiry|timestamp',
		'bgstart' => $lastBlockAdjusted,
		'bgdir' => 'newer'
	);
	$blocks = $meta->listHandler( $query );

	echo "\n-----------------\nInitiated at " . date( 'c' ) . "\n" . count( $blocks ) . " blocks found...\n";

	if( count( $blocks ) === 0 ) die( "\n-----------------\n" );

	// Suppress "alreadyblocked" warning messages
	global $pgVerbose;
	$pgVerbose = array( 4 );

	$ipNumbers = array();
	foreach( $blocks as $block ){
		$params = array( 'nocreate' );

		$isRangeBlock = ( strpos( $block['address'], '/' ) !== false );
		$rangeString = $isRangeBlock ? ' range' : '';

		$commonBits = 32;
		$address = $block['address'];
		if( strpos( $address, ':' ) !== false ) continue; // IPv6

		if( $isRangeBlock ) {
			list( $address, $commonBits ) = explode( '/', $address );
			$commonBits = intval( $commonBits );
		}

		if( isset( $block['anononly'] ) ) $params[] = 'anononly';
		if( strtotime( $block['expiry'] ) < time() ) continue;

		$user = initUser( $address );
		$summary = "Mirror WMF [[:meta:Global_blocks|global block]] of this IP address$rangeString ([[User:Global block importer bot|what does that mean?]], [[User:Global block importer bot|appeals]])";
		if( $user->block( $summary, $block['expiry'], $params, false, ( $commonBits == 32 ) ? null : $commonBits ) ) {
			echo $block['address'] . " blocked.\n";
			$ipNumbers[] =  pow( 2, ( 32 - $commonBits ) );
		}
	}

	echo "Blocked " . array_sum( $ipNumbers ) . " blocked IPs across " . count( $ipNumbers ) . " ranges.\n";
	echo "Distribution: " . str_replace( "\n", ' ', print_r( array_count_values( $ipNumbers ), true ) );
	echo "\n-----------------\n";