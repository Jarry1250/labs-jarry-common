<?php
	/**
	 * Global functions © 2011-14
	 * @author Harry Burt <jarry1250@gmail.com>
	 *
	 * @todo fix getstatus for Labs
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

	ini_set( 'user_agent', 'Wikimedia Labs tool' );
	ini_set( 'display_errors', 1 );
	error_reporting( E_ALL );

	require_once( '/data/project/jarry-common/public_html/i18n.php' );
	require_once( '/data/project/jarry-common/public_html/database.php' );

	// Now (hackily) give us access to Peachy's helpful HTTP library
	define( 'PEACHYVERSION', 2 );
	require_once( '/data/project/jarry-common/public_html/peachy/Includes/Hooks.php' );
	require_once( '/data/project/jarry-common/public_html/peachy/HTTP.php' );

	function get_html( $str, $one = '', $two = '' ) {
		global $I18N;
		$lang = $I18N->getLang();
		$html = '';
		switch( $str ){
			case "header":
				$onesan = preg_replace( '/[<][^>]+[>]/', '', $one );
				$emptyMessage = _html( 'error-form-empty', 'jarry' );
				$errorHeading = _html( 'error', 'general' );
				$dir = method_exists( $I18N, 'getDir' ) ? "dir='{$I18N->getDir()}'" : '';
				$htmltag = "<html xmlns='http://www.w3.org/1999/xhtml' $dir lang='$lang' xml:lang='$lang'>";
				$html = <<<EOT
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
$htmltag
	<head>
		<title>Jarry1250's Toolserver Tools - $onesan</title>
		<meta http-equiv="Content-Type" content="charset=UTF-8" />
		<link rel="stylesheet" type="text/css" media="screen, projection" href="//tools.wmflabs.org/jarry-common/master.css" />
		<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
		<script src="//tools.wmflabs.org/jarry-common/jquery.html5form-min.js"></script>
		<script src="//tools.wmflabs.org/jarry-common/jquery.cookie-min.js"></script>
		<script type="text/javascript">
			function dismiss(){
				$('#statusbox').hide();
				var name = $('#statusbox').attr('name');
				$.cookie('jarry-status', name, { expires: 1, path: '/' });
			}
			$( document ).ready( function(){
				var forms = document.getElementsByTagName('form');
				if( forms.length > 0 ){
					$( "form" ).html5form( {
						appendTo : '#moretools',
						emptyMessage : '$emptyMessage',
						errorHeading : '$errorHeading'
					} );
				}
				$('#formerror').hide();

				var statusbox = $('#statusbox');
				if( statusbox != undefined ){
					$('#dismiss').show();
					$('#dismiss a').show();
					if($.cookie('jarry-status') == statusbox.attr('name')){
						statusbox.hide();
					}
				}
			} );
		</script>
	</head>
	<body>
		<div id="header">
			<h1><a href="http://tools.wmflabs.org/jarry-common"><span class="b">J</span>arry1250's <span class="b">T</span>ools<img src="//tools.wmflabs.org/jarry-common/labs-logo.png" title="Powered by Wikimedia Labs" border="0" width="32px" alt="Powered by Wikimedia Labs"></a></h1>
			<h2>$onesan</h2>
		</div>
			<div id="content">
EOT;
				$html .= "\t\t\t" . get_status() . "\n";
				break;
			case "footer":
				$html = '';
				if( strlen( $one ) > 0 ){
					$html = "\n\t\t\t<br style='clear:both'/><hr />\n\t\t\t$one";
				}
				$html .= "\n\t\t</div>\n\t\t<div id='footer'>";
				$html .= $I18N->getPromoBox();
				$html .= "\n\t\t\t\t<p id='footerleft'>";
				$html .= _html(
							 'last-modified-date', array(
								 'domain' => 'general',
								 'variables' => array( $I18N->dateFormatted( filemtime( realpath( $_SERVER["SCRIPT_FILENAME"] ) ) ) )
							 )
						 ) . ". ";
				$html .= _html( 'options', 'general' ) . ': <a href="https://github.com/Jarry1250/labs-' . substr( dirname( $_SERVER["PHP_SELF"] ), 1 ) . '/">' . strtolower( _html( 'view-source', 'general' ) ) . '</a> &ndash; ';
				$html .= '<a href="https://bugzilla.wikimedia.org/enter_bug.cgi?product=Tool%20Labs%20tools" target="_blank">' . _html( 'bugs', 'jarry' ) . '</a> &ndash; ';
				$html .= '<a href="//en.wikipedia.org/wiki/User_talk:Jarry1250" target="_blank">' . _html( 'comments', 'jarry' ) . '</a>';
				if( file_exists( dirname( realpath( $_SERVER["SCRIPT_FILENAME"] ) ) . "/doc/index.php" ) ){
					$html .= ' &ndash; <a href="doc/index.php" target="_blank">' . _html( 'forking', 'jarry' ) . '</a>';
				}
				$html .= ".</p>\n\t\t\t\t<br style='clear:both; display:none;'/>";
				$html .= "\n\t\t</div>\n\t</body>\n</html>";
				break;
		}
		return $html;
	}

	function error( $message, $title, $footer = '' ) {
		$html = get_html( 'header', $title );
		$html .= "\t\t<div class='error'>\n";
		$html .= "\t\t\t<h3>" . _html( 'error', 'general' ) . "</h3>\n";
		$html .= "\t\t\t<p>$message</p>\n";
		$html .= "\t\t</div>\n";
		$html .= get_html( 'footer', $footer );
		die( $html );
	}

	function get_status() {
		return '';
		/*	$html = '';
			$id = 'false/';
			$ctx = stream_context_create( array( 'http' => array( 'timeout' => 5 ) ) );

			$old = ini_set('default_socket_timeout', 5);
			try{
				$statusdata = file_get_contents( 'https://toolserver.org/tsstatus/json', false, $ctx );
			} catch(Exception $e) { $statusdata = false; }
			if( $statusdata ){
				$statusdata = json_decode( $statusdata, true );
				foreach( $statusdata['statuses'] as $status ){
					if( $status['@status'] !== 'OK' ){
						$html .= '<p>' . _html( 'error-status', 'jarry' ) .  ' ' . _html( 'error-output-impaired', 'jarry' ) . '</p>';
						$id = 'true/';
						break;
					}
				}
			}


			$errored = array();
			$lagged = array();
			try{
			$replagdata = file( 'https://toolserver.org/~daniel/WikiSense/replag.php?format=text', false, $ctx );
			} catch(Exception $e) {}
			ini_set('default_socket_timeout', $old);
			if( !is_array( $replagdata ) ) return '';

			foreach ( $replagdata as $cluster ){
				list( $identifier, $status, $lag ) = explode( "\t", $cluster );
				if( intval( $lag ) > 60 ){
					$lagged['S' . $identifier] = $lag;
				}
				if( $status !== 'OK' ){
					$errored['S' . $identifier] = $status;
				}
			}
			if( count( $errored ) > 0 ){
				$html .= '<p>' . _html( 'error-server-status', array( 'variables' => array( implode( ', ', array_keys( $errored ) ) ), 'domain' => 'jarry' ) ) . ' ' . _html( 'error-output-impaired', 'jarry' ) . '</p>';
				$html .= '<ul>';
				foreach ( $errored as $cluster => $status ) {
					$html .= '<li>' . _html( 'server-desc-' . strtolower( $cluster ), 'jarry' );
					$html .= ' ' . _html( 'error-current-status', array( 'variables' => array( $status ), 'domain' => 'jarry' ) );
					$html .= '</li>';
				}
				$html .= '</ul>';
				$id .= implode( ', ', array_keys( $errored ) );
			}
			$id .= '/';
			if( count( $lagged ) > 0 ){
				$html .= '<p>' . _html( 'error-server-lag', array( 'variables' => array( implode( ', ', array_keys( $lagged ) ) ), 'domain' => 'jarry' ) ) . ' ' . _html( 'error-output-impaired', 'jarry' ) . '</p>';
				$html .= '<ul>';
				foreach ( $lagged as $cluster => $lag ) {
					$html .= '<li>' . _html( 'server-desc-' . strtolower( $cluster ), 'jarry' );
					$html .= ' ' . _html( 'error-lag-duration', array( 'variables' => array( $lag ), 'domain' => 'jarry' ) );
					$html .= '</li>';
				}
				$html .= '</ul>';
				$id .= implode( ', ', array_keys( $lagged ) );
			}
			if( strlen( $html ) === 0 ){
				return '';
			} else {
				return '<div id="statusbox" name="status/'.$id.'/">' . "\n$html\n" . '<p id="dismiss"><a href="javascript:dismiss();"><span>Dismiss</span> <img src="https://upload.wikimedia.org/wikipedia/commons/0/0c/Fileclose.png" style="width:20px;" /></a></p>'."\n".'</div>';
			}	*/
	}

	function getNamespaces( $langcode, $projectcode ) {
		$http = HTTP::getDefaultInstance();
		$apiUrl = "https://$langcode.$projectcode.org/w/api.php?format=json&action=query&meta=siteinfo&siprop=namespaces";
		$json = json_decode( $http->get( $apiUrl ), true );

		if( $json == false || count( $json ) == 0 ) return array();

		$namespaces = array();
		foreach( $json['query']['namespaces'] as $key => $namespace ){
			if( $key < 0 ) continue;
			$namespaces[$key] = $namespace['*'];
		}
		return $namespaces;
	}

	function getNamespaceSelect( $langcode, $default, $projectcode = 'wikipedia' ) {
		$namespaces = getNamespaces( $langcode, $projectcode );
		$html = "<select name=\"namespace\" width=\"40\">\n";
		foreach( $namespaces as $code => $name ){
			if( $name == '' ){
				$name = _html( 'namespace-main', 'jarry' );
			}
			$html .= "\t<option value=\"$code\"";
			if( $code == $default ){
				$html .= " selected='selected'";
			}
			$html .= ">$name</option>\n";
		}
		$html .= "</select>";
		return $html;
	}

	function get_databasename( $langcode, $projectcode, $separator = '-' ) {
		if( $projectcode == 'wikipedia' ){
			$projectcode = 'wiki';
		}
		if( $projectcode == 'wikimedia' ){
			$projectcode = 'wiki';
		}
		return $langcode . $projectcode . $separator . "p";
	}

	class Counter {
		const PATH = '/data/project/jarry-common/counters/';
		public static function getCounter( $filename ) {
			$filepath = self::PATH . $filename;
			if( !file_exists( $filepath ) ){
				$dirname = dirname( $filename );
				if( !is_dir( $dirname ) ) mkdir( $dirname, 2770, true );

				file_put_contents( $filepath, '1' );
				return 1;
			}
			$count = file_get_contents( $filepath );
			return intval( $count );
		}

		public static function increment( $filename ) {
			$current = self::getCounter( $filename );
			file_put_contents( self::PATH . $filename, ( $current + 1 ) );
		}
	}