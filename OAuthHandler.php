<?php
	/**
	 * Written in 2013 by Brad Jorsch. Some edits in the same year by Harry Burt
	 * <http://harryburt.co.uk>
	 *
	 * To the extent possible under law, the author(s) have dedicated all copyright
	 * and related and neighboring rights to this software to the public domain
	 * worldwide. This software is distributed without any warranty.
	 *
	 * See <http://creativecommons.org/publicdomain/zero/1.0/> for a copy of the
	 * CC0 Public Domain Dedication.
	 */
	class OAuthHandler {
		private $consumerSecret;
		private $consumerKey;
		private $tokenSecret;
		private $tokenKey;
		private $userAgent = 'Wikimedia Labs/jarry-common OAuth Handler';
		private $mwOAuthUrl = 'https://www.mediawiki.org/w/index.php?title=Special:OAuth';
		private $apiUrl;

		public function __construct( $params ){
			$this->consumerSecret = $params['consumerSecret'];
			$this->consumerKey = $params['consumerKey'];
			$this->apiUrl = $params['apiUrl'];

			session_start();
			if ( isset( $_SESSION['tokenKey'] ) ) {
				$this->tokenKey = $_SESSION['tokenKey'];
				$this->tokenSecret = $_SESSION['tokenSecret'];
			}
			session_write_close();
		}

		/**
		 * Utility public function to sign a request
		 *
		 * Note this doesn't properly handle the case where a parameter is set both in
		 * the query string in $url and in $params, or non-scalar values in $params.
		 *
		 * @param string $method Generally "GET" or "POST"
		 * @param string $url URL string
		 * @param array $params Extra parameters for the Authorization header or post
		 *    data (if application/x-www-form-urlencoded).
		 * @return string Signature
		 */
		public function signRequest( $method, $url, $params = array() ) {
			$parts = parse_url( $url );

			// We need to normalize the endpoint URL
			$scheme = isset( $parts['scheme'] ) ? $parts['scheme'] : 'http';
			$host = isset( $parts['host'] ) ? $parts['host'] : '';
			$port = isset( $parts['port'] ) ? $parts['port'] : ( $scheme == 'https' ? '443' : '80' );
			$path = isset( $parts['path'] ) ? $parts['path'] : '';
			if( ( $scheme == 'https' && $port != '443' ) ||
				( $scheme == 'http' && $port != '80' )
			){
				// Only include the port if it's not the default
				$host = "$host:$port";
			}

			// Also the parameters
			$pairs = array();
			parse_str( isset( $parts['query'] ) ? $parts['query'] : '', $query );
			$query += $params;
			unset( $query['oauth_signature'] );
			if( $query ){
				$query = array_combine(
				// rawurlencode follows RFC 3986 since PHP 5.3
					array_map( 'rawurlencode', array_keys( $query ) ),
					array_map( 'rawurlencode', array_values( $query ) )
				);
				ksort( $query, SORT_STRING );
				foreach( $query as $k => $v ){
					$pairs[] = "$k=$v";
				}
			}

			$toSign = rawurlencode( strtoupper( $method ) ) . '&' .
					  rawurlencode( "$scheme://$host$path" ) . '&' .
					  rawurlencode( join( '&', $pairs ) );
			$key = rawurlencode( $this->consumerSecret ) . '&' . rawurlencode( $this->tokenSecret );
			return base64_encode( hash_hmac( 'sha1', $toSign, $key, true ) );
		}

		/**
		 * Request authorization
		 * @return void
		 */
		protected function doAuthorizationRedirect() {
			// First, we need to fetch a request token.
			// The request is signed with an empty token secret and no token key.
			$this->tokenSecret = null;
			$this->tokenKey = null;
			$url = $this->mwOAuthUrl . '/initiate';
			$url .= strpos( $url, '?' ) ? '&' : '?';
			$url .= http_build_query(
				array(
					'format'                 => 'json',

					// OAuth information
					'oauth_callback'         => 'oob', // Must be "oob" for MWOAuth
					'oauth_consumer_key'     => $this->consumerKey,
					'oauth_version'          => '1.0',
					'oauth_nonce'            => md5( microtime() . mt_rand() ),
					'oauth_timestamp'        => time(),

					// We're using secret key signatures here.
					'oauth_signature_method' => 'HMAC-SHA1',
				)
			);
			$signature = $this->signRequest( 'GET', $url );
			$url .= "&oauth_signature=" . urlencode( $signature );
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			//curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$data = curl_exec( $ch );
			if( !$data ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
				exit( 0 );
			}
			curl_close( $ch );
			$token = json_decode( $data );
			if( is_object( $token ) && isset( $token->error ) ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
				exit( 0 );
			}
			if( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Invalid response from token request';
				exit( 0 );
			}

			// Now we have the request token, we need to save it for later.
			session_start();
			$_SESSION['tokenKey'] = $token->key;
			$_SESSION['tokenSecret'] = $token->secret;
			session_write_close();

			// Then we send the user off to authorize
			$url = $this->mwOAuthUrl . '/authorize';
			$url .= strpos( $url, '?' ) ? '&' : '?';
			$url .= http_build_query(
				array(
					'oauth_token'        => $token->key,
					'oauth_consumer_key' => $this->consumerKey,
				)
			);
			header( "Location: $url" );
			echo 'Please see <a href="' . htmlspecialchars( $url ) . '">' . htmlspecialchars( $url ) . '</a>';
		}

		/**
		 * Handle a callback to fetch the access token
		 * @return void
		 */
		public function fetchAccessToken() {
			$url = $this->mwOAuthUrl . '/token';
			$url .= strpos( $url, '?' ) ? '&' : '?';
			$url .= http_build_query(
				array(
					'format'                 => 'json',
					'oauth_verifier'         => $_GET['oauth_verifier'],

					// OAuth information
					'oauth_consumer_key'     => $this->consumerKey,
					'oauth_token'            => $this->tokenKey,
					'oauth_version'          => '1.0',
					'oauth_nonce'            => md5( microtime() . mt_rand() ),
					'oauth_timestamp'        => time(),

					// We're using secret key signatures here.
					'oauth_signature_method' => 'HMAC-SHA1',
				)
			);
			$signature = $this->signRequest( 'GET', $url );
			$url .= "&oauth_signature=" . urlencode( $signature );
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, $url );
			//curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$data = curl_exec( $ch );
			if( !$data ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
				exit( 0 );
			}
			curl_close( $ch );
			$token = json_decode( $data );
			if( is_object( $token ) && isset( $token->error ) ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Error retrieving token: ' . htmlspecialchars( $token->error );
				exit( 0 );
			}
			if( !is_object( $token ) || !isset( $token->key ) || !isset( $token->secret ) ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Invalid response from token request';
				exit( 0 );
			}

			// Save the access token
			session_start();
			$_SESSION['tokenKey'] = $this->tokenKey = $token->key;
			$_SESSION['tokenSecret'] = $this->tokenSecret = $token->secret;
			session_write_close();
		}


		/**
		 * Send an API query with OAuth authorization
		 *
		 * @param array $post Post data
		 * @param object $ch Curl handle
		 * @return array API results
		 */
		public function doApiQuery( $post, $additionalHeaders = array(), &$ch = null ) {
			$headerArr = array(
				// OAuth information
				'oauth_consumer_key'     => $this->consumerKey,
				'oauth_token'            => $this->tokenKey,
				'oauth_version'          => '1.0',
				'oauth_nonce'            => md5( microtime() . mt_rand() ),
				'oauth_timestamp'        => time(),

				// We're using secret key signatures here.
				'oauth_signature_method' => 'HMAC-SHA1',
			);
			$signature = $this->signRequest( 'POST', $this->apiUrl, $post + $headerArr );
			$headerArr['oauth_signature'] = $signature;

			$header = array();
			foreach( $headerArr as $k => $v ){
				$header[] = rawurlencode( $k ) . '="' . rawurlencode( $v ) . '"';
			}
			$additionalHeaders[] = 'Authorization: OAuth ' . join( ', ', $header );
			if( $post['action'] !== 'upload' ){
				$post = http_build_query( $post );
			}
			if( !$ch ){
				$ch = curl_init();
			}
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_URL, $this->apiUrl );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $post  );
			curl_setopt( $ch, CURLOPT_HTTPHEADER, $additionalHeaders );
			//curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, false );
			curl_setopt( $ch, CURLOPT_USERAGENT, $this->userAgent );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			$data = curl_exec( $ch );
			if( !$data ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Curl error: ' . htmlspecialchars( curl_error( $ch ) );
				exit( 0 );
			}
			$ret = json_decode( $data );
			if( $ret === null ){
				header( "HTTP/1.1 500 Internal Server Error" );
				echo 'Unparsable API response: <pre>' . htmlspecialchars( $data ) . '</pre>';
				exit( 0 );
			}
			return $ret;
		}

		public function authorizeMe(){
			// We're going to test our authorization status using a simple API call
			$queryParams = array(
				'format' => 'json',
				'action' => 'query',
				'meta' => 'userinfo',
			);

			// First fetch the username
			$ch = null;
			$res = $this->doApiQuery( $queryParams, $ch );

			if ( isset( $res->error->code ) && $res->error->code === 'mwoauth-invalid-authorization' ) {
				// We're not authorized!
				$this->doAuthorizationRedirect();

				// And retry
				$res = $this->doApiQuery( $queryParams, $ch );
			}
			return $res->query->userinfo->name;
		}
	}