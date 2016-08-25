<?php

/**
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 */
class PhpSession implements SessionInterface
{
	const SESSION_NAME = 'ALLEGRORESTAPI';

    /**
     * Access token required for all request in context of user
     * @var string
     */
	protected $accessToken;

    /**
     * Refresh token used for refreshing session every 24h
     * @var string
     */
	protected $refreshToken;

    /**
     * Time of end current session (Unix time stamp)
     * @var int
     */
	protected $expiresTime;


	public function __construct()
	{
        if (version_compare(phpversion(), '5.4.0', '<')) {
             if(session_id() == '') {
                session_start();
             }
         } else {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }
         }
	}

    public function start($accessData)
    {
    	if(!$accessData ||
    		!isset($accessData['accessToken']) ||
    		!isset($accessData['refreshToken']) ||
    		!isset($accessData['expiresTime']))
    		throw new SessionException('Invalid session data', 1);

    	// End current session if exists
    	$this->end();

    	$this->accessToken = $accessData['accessToken'];
    	$this->refreshToken = $accessData['refreshToken'];
    	$this->expiresTime = $accessData['expiresTime'];
    		
    	return $_SESSION[self::SESSION_NAME] = (array)$accessData;
    }

    public function init()
    {
    	if (isset($_SESSION[self::SESSION_NAME]))
    		return $this->start($_SESSION[self::SESSION_NAME]);
    	return false;
    }

    public function end()
    {
    	unset($this->accessToken, $this->refreshToken, $this->expiresTime, $_SESSION[self::SESSION_NAME]);
    	return true;
    }

    public function getAccessToken()
    {
    	return $this->accessToken;
    }

    public function getRefreshToken()
    {
    	return $this->refreshToken;
    }

    public function getExpiresTime()
    {
    	return $this->expiresTime;
    }
}