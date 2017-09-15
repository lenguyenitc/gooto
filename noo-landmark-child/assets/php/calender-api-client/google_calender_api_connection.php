<?php

require_once __DIR__ . '/vendor/autoload.php';

define( 'APPLICATION_NAME', 'GOOTO booking via Google Calendar API' );
define( 'CREDENTIALS_PATH', __DIR__ . '/credentials/calendar-access-tokens.json' );
define( 
	'SCOPES', 
	implode( ' ', 
		array( 
			Google_Service_Calendar::CALENDAR,
			Google_Service_Calendar::CALENDAR_READONLY,
			Google_Service_Directory::ADMIN_DIRECTORY_RESOURCE_CALENDAR,
			Google_Service_Directory::ADMIN_DIRECTORY_RESOURCE_CALENDAR_READONLY,
		) 
	) 
);

/**
* 
*/
class Google_Calender_API_Connection {

	protected static $_instance	= null;
	protected static $_client 	= null;
	protected $auth_url 		= null;

	public static function get_instance() {
		if( self::$_instance == null ) {
			self::$_instance = new Google_Calender_API_Connection();
		}

		return self::$_instance;
	}

	protected function __construct() {
		$bookings_setting 	= get_option( 'bookings_setting' );
		$google_calender 	= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';
		$secret_uploaded	= isset( $google_calender['secret_uploaded'] ) ? $google_calender['secret_uploaded'] : '';
		$client_secret_file	= isset( $google_calender['client_secret_file'] ) ? $google_calender['client_secret_file'] : '';

		if( $secret_uploaded && $client_secret_file ) {
			define( 'CLIENT_SECRET_PATH', __DIR__ . '/' . $client_secret_file );
		}
	}

	public function Google_Client() {
		if( defined( 'CLIENT_SECRET_PATH' ) ) {
			if( !file_exists( CLIENT_SECRET_PATH ) ) {
				throw new Exception( "CLIENT SECRET FILE MISSING" );			
			} elseif ( self::$_client == null ) {

				self::$_client = new Google_Client();
				self::$_client->setApplicationName( APPLICATION_NAME );
				self::$_client->setScopes( SCOPES );
				self::$_client->setAuthConfig( CLIENT_SECRET_PATH );
				self::$_client->setAccessType( 'offline' );
				self::$_client->setApprovalPrompt( 'force' );

				// Load previously authorized credentials from a file.
				$credentialsPath = $this->expandHomeDirectory( CREDENTIALS_PATH );

				if( file_exists( $credentialsPath ) ) {
					$accessToken = json_decode( file_get_contents( $credentialsPath ), true );
				} else {
					// Request authorization from the user.
					$authUrl = self::$_client->createAuthUrl();

					// echo "Open the following link in your browser:" . "<br/><a href='" . $authUrl . "'>" . $authUrl . "</a>";
					$bookings_setting 	= get_option( 'bookings_setting' );
					$google_calender 	= isset( $bookings_setting['google_calender'] ) ? $bookings_setting['google_calender'] : '';
					$auth_code 			= isset( $google_calender['auth_code'] ) ? $google_calender['auth_code'] : '';

					if( !empty( $auth_code ) ) {
						// Exchange authorization code for an access token.
						$accessToken = self::$_client->fetchAccessTokenWithAuthCode( $auth_code );

						if( !$accessToken instanceof Exception ) {

							// Store the credentials to disk.
							if( !file_exists( dirname( $credentialsPath ) ) ) {
								mkdir( dirname( $credentialsPath ), 0700, true);
							}
							file_put_contents( $credentialsPath, json_encode( $accessToken ) );
						}
					} else {
						$accessToken = null;						
					}
				}

				if( $accessToken ) {
					self::$_client->setAccessToken( $accessToken );

					// Refresh the token if it's expired.
					if( self::$_client->isAccessTokenExpired() ) {
						self::$_client->fetchAccessTokenWithRefreshToken( self::$_client->getRefreshToken() );
						file_put_contents( $credentialsPath, json_encode( self::$_client->getAccessToken() ) );
					}
				}
			}
		} else {
			throw new Exception( "CLIENT SECRET PATH NOT DEFINED" );
		}

		return self::$_client;
	}

	private function expandHomeDirectory($path) {
		$homeDirectory = getenv( 'HOME' );
		if( empty( $homeDirectory ) ) {
			$homeDirectory = getenv( 'HOMEDRIVE' ) . getenv( 'HOMEPATH' );
		}
		return str_replace( '~', realpath( $homeDirectory ), $path );
	}

	public function Google_Service_Calendar( $client = null ) {
		if( $client == null ) {
			$client = $this->Google_Client();
		}

		return new Google_Service_Calendar( $client );
	}

	public function Google_Service_Directory( $client = null ) {
		if( $client == null ) {
			$client = $this->Google_Client();
		}

		return new Google_Service_Directory( $client );
	}

	public function Google_Service_Calendar_Event( $event = array() ) {
		return new Google_Service_Calendar_Event( $event );
	}
}


?>