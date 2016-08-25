<?php

/**
 * An object used for autheticate user in API. Allows fetching, saving and
 * deleting resource objects in context of logged client.
 *
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 */
class AllegroRestApiClient extends AllegroRestApi
{
    const SESSION_FILESYSTEM = 'filesystem';
    const SESSION_PHPSESSION = 'phpsession';

    /**
     * Access token required for all request in context of user
     * @var string
     */
	protected $_session;

    /**
     * Constructor
     *
     * @param bool $sandbox			Whether to use sandbox API or regular API
     * @param bool $sessionType   	Session type SESSION_FILESYSTEM or SESSION_PHPSESSION
     */
	function __construct($sandbox = false, $sessionType = self::SESSION_PHPSESSION)
	{
        switch($sessionType) {
            case self::SESSION_PHPSESSION :
                $this->_session = new PhpSession();
            break;
        
            case self::SESSION_FILESYSTEM :
                $this->_session = new FilesystemSession();
            break;
        }

		parent::__construct($sandbox);
	}

    /**
     * Start new session, authenticate with Oauth code and gets access token
     *
     * @param bool $OauthCode		Oauth code
     */
	public function startSession($OauthCode)
	{
		$accessData = $this->request(
			$this->getAuthUrl(
				'token',
				array(
					'grant_type' 	=> 'authorization_code',
					'code' 			=> $OauthCode,
					'api-key' 		=> API_KEY,
					'redirect_uri' 	=> APP_URL
				)
			),
			array('Authorization: Basic '.base64_encode(CLIENT_ID.':'.CLIENT_SECRET))
		);
		return $this->_session->start(array(
			'accessToken' => $accessData->access_token,
			'refreshToken' => $accessData->refresh_token,
			'expiresTime' => (int)$accessData->expires_in + time()));
	}

    /**
     * Init existing session, refreshig if access token expired
     *
     */
	public function initSession()
	{
		if($this->_session->init()) {
			// If access token expired get new one
			if($this->_session->getExpiresTime() < time()) {
				$accessData = $this->request(
					$this->getAuthUrl(
						'token',
						array(
							'grant_type' 	=> 'refresh_token',
							'refresh_token' => $this->_session->getRefreshToken(),
							'api-key' 		=> API_KEY,
							'redirect_uri' 	=> APP_URL
						)
					),
					array('Authorization: Basic '.base64_encode(CLIENT_ID.':'.CLIENT_SECRET))
				);
				return $this->_session->start(array(
					'accessToken' => $accessData->access_token,
					'refreshToken' => $accessData->refresh_token,
					'expiresTime' => (int)$accessData->expires_in + time()));
			}
			return true;
		}
		return false;	
	}

    /**
     * Get session expire time (Unix timestamp)
     *
     */
	public function getSessionExpiresTime()
	{
		return $this->_session->getExpiresTime();
	}
}