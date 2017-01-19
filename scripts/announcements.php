<?php
	/*
	WikiProject announcements distributor
	© 2011 and 2013 Harry Burt <jarry1250@gmail.com>

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

	require_once( '/data/project/jarry-common/public_html/peachy/Init.php' );
	$filename = '/data/project/jarry-common/public_html/scripts/announcements.txt';

	$wiki = Peachy::newWiki( 'livingbot' );
	$page = initPage( 'Wikipedia:Announcements' );

	$updateonly = ( isset( $_GET['update'] ) && $_GET['update'] == "y" );
	$human = ( isset( $_GET['check'] ) && $_GET['check'] == "y" );

	// Load list of projects to check
	$lines = file( $filename );
	$projects = array();
	// Top line as a header.
	$newfile = array_shift( $lines );
	foreach( $lines as $line ){
		$projects[] = explode( ",", trim( $line ) );
	}
	echo "<ol>\n";
	$messages = array();
	// Loop through each project
	for( $i = 0; $i < count( $projects ); $i++ ){
		// Break down project info into human-readable variables
		$project = trim( $projects[$i][0] );
		list( $namespace, $identifier ) = explode( ":", $projects[$i][1] );
		$oldcount = trim( $projects[$i][2] );
		$commas = strposall( $lines[$i], "," );

		echo "<li>$identifier</li>\n";
		if( $updateonly ){
			if( $oldcount == -1 ){
				echo "Updating a new item. ";
				$newcount = get_count( $namespace, $identifier );
				$newfile .= substr( $lines[$i], 0, $commas[1] ) . ", " . $newcount . substr( $lines[$i], $commas[2] );
			} else {
				$newfile .= $lines[$i];
			}
			continue;
		} else {
			// Get the next milestone target
			for( $j = ( count( $projects[$i] ) - 1 ); $j > 2; $j-- ){
				if( $oldcount < $projects[$i][$j] ){
					$nextmilestone = trim( $projects[$i][$j] );
				}
			}
			// If no next milestone, move on
			if( !isset( $nextmilestone ) ){
				continue;
			}
			// Get the new count
			$newcount = get_count( $namespace, $identifier );
			if( $newcount > $oldcount ){
				echo "Up from $oldcount to $newcount. \n";
			} else {
				echo "Stuck on ($oldcount/$newcount).";
			}
			// And compare
			if( $newcount >= $nextmilestone ){
				echo "<strong>This project has an announcement to make. </strong>\n";
				$identifier = trim( str_replace( "_", " ", $identifier ) );
				// Chip off a milestone from the file
				$newfile .= substr( $lines[$i], 0, $commas[1] ) . ", " . $newcount . substr( $lines[$i], $commas[3] );
				// Based on namespace, write appropriate message
				switch( trim( $namespace ) ){
					case "on-template":
						$code = substr( $identifier, 0, 2 );
						switch( $code ){
							case "FA":
								$messages[] = "The English Wikipedia now has " . $nextmilestone . " or more [[Wikipedia:Featured articles|featured articles]].";
								break;
							case "GA":
								$messages[] = "The English Wikipedia now has " . $nextmilestone . " or more [[Wikipedia:Good articles|good articles]].";
								break;
							default:
								$messages[] = "The English Wikipedia now has " . $nextmilestone . " or more [[Wikipedia:" . $code . "|" . $code . "]].";
								break;
						}
						break;
					case "total":
						$messages[] = $project . " now covers " . $nextmilestone . " or more articles in total.";
						break;
					case "good topics":
						$messages[] = "The English Wikipedia now has " . $nextmilestone . " or more articles contained within [[Wikipedia:Good topics|good topics]].";
						break;
					case "featured topics":
						$messages[] = "The English Wikipedia now has " . $nextmilestone . " or more articles contained within [[Wikipedia:Featured topics|featured topics]].";
						break;
					case "Category":
						$messages[] = $project . " passed " . $nextmilestone . " articles in its category [[:Category:" . $identifier . "|" . $identifier . "]].";
						break;
					case "cat-only":
						$messages[] = "[[:Category:" . $identifier . "|" . $identifier . "]]  passed " . $nextmilestone . " articles.";
						break;
					case "Template":
						$messages[] = "[[Wikipedia:" . $project . "|" . $project . "]] passed " . $nextmilestone . " articles using its template [[Template:" . $identifier . "|" . $identifier . "]].";
						break;
					case "good":
						$messages[] = $project . " now includes " . $nextmilestone . " or more [[Wikipedia:Good articles|good articles]].";
						break;
					case "featured":
						$messages[] = $project . " now includes " . $nextmilestone . " or more [[Wikipedia:Featured articles|featured articles]].";
						break;
					default:
						// Mostly "A-Class", "B-Class" and so forth
						$messages[] = $project . " now includes " . $nextmilestone . " or more " . $namespace . " articles.";
						break;
				}
			} else {
				// Just update the count, leave milestones intact
				$newfile .= substr( $lines[$i], 0, $commas[1] ) . ", " . $newcount . substr( $lines[$i], $commas[2] );
			}
		}
	}
	echo "</ol>";

	if( $updateonly ){
		// Update list of projects with new counts, ready for next time.
		file_put_contents( $filename, $newfile );
	} else {
		// If milestones have been passed...
		if( count( $messages ) == 0 ){
			die( "No project passed one of its milestones." );
		}
		// ...update WP:ANN
		$post = "";
		$date = date( "j F Y" );
		// Get existing page
		$pagetext = $page->get_text();
		// Draw up the post
		if( preg_match( ( "/===[ ]*" . $date . "[ ]*===/" ), $pagetext, $temp ) ){
			$existsat = stripos( $pagetext, $temp[0] ) + strlen( $temp[0] );
		} else {
			$post = "=== " . $date . " ===\n";
		}
		for( $i = 0; $i < count( $messages ); $i++ ){
			if( stripos( $pagetext, $messages[$i] ) !== false || stripos( $post, $messages[$i] ) != false ){
				// For some reason, this post has been before
				continue;
			}
			$post .= $messages[$i] . "\n";
			if( $i !== ( count( $messages ) - 1 ) ){
				$post .= "\n";
			}
		}
		if( strlen( $post ) < 3 ){
			die( "Would have been a null edit." );
		}
		// If the date header has already been added for today
		if( isset( $existsat ) ){
			// Add below that
			$newpage = substr( $pagetext, 0, $existsat ) . "\n" . $post . substr( $pagetext, $existsat );
		} else {
			// Else add under the main heading
			$heading = '==Milestones==';
			$existsat = stripos( $pagetext, $heading ) + strlen( $heading );
			$newpage = substr( $pagetext, 0, $existsat ) . "\n" . $post . substr( $pagetext, $existsat );
		}
		// Write changes
		if( $human ){
			echo "<pre>$post</pre>";
		} else {
			if( $page->edit( $newpage, "Bot adding milestone(s) from WikiProject(s). [[User_talk:LivingBot|Incorrect?]] Or do you want to [[User:LivingBot/ProjectSignup|sign up your project]]?" ) !== false ){
				// Update list of projects with new counts, ready for next time.
				file_put_contents( $filename, $newfile );
			}
		}
	}

	// ******* HELPER FUNCTIONS ******* \\

	function get_count( $namespace, $identifier ) {
		// Get the new count of this category/template/whatever
		switch( $namespace ){
			case "on-template":
				// This way is messier, but more reliable, than grabbing the Wiki edit page because of protection
				$identifier = str_replace( " ", "_", $identifier );
				$page = file_get_contents( "https://en.wikipedia.org/wiki/Template:" . $identifier );
				preg_match( "/[^0-9a-zA-Z]p[^0-9a-zA-Z]([0-9]{4,6})[^0-9a-zA-Z!]\/p[^0-9a-zA-Z!]/", $page, $matches );
				return $matches[1];
				break;
			case "Category":
				// fall through
			case "total":
				// fall through
			default:
				$identifier = urlencode( $identifier );
				$page = json_decode( file_get_contents( 'https://en.wikipedia.org/w/api.php?format=json&action=query&prop=categoryinfo&titles=Category:' . $identifier ), true );
				try {
					$page = array_shift( $page['query']['pages'] );
					return $page['categoryinfo']['pages'];
				} catch( Exception $e ) {
					return -1;
				}
		}
	}

	function strposall( $haystack, $needle ) {
		// Simple helper function for getting all occurences in string
		$s = 0;
		$i = 0;
		while( is_integer( $i ) ){
			$i = strpos( $haystack, $needle, $s );
			if( is_integer( $i ) ){
				$aStrPos[] = $i;
				$s = $i + strlen( $needle );
			}
		}
		if( isset( $aStrPos ) ){
			return $aStrPos;
		} else {
			return false;
		}
	}