<?php

/**
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 */
class FilesystemSession implements SessionInterface
{
	const SESSION_FILE = 'sessions/session.json';

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
		if(!is_writable(dirname(self::SESSION_FILE)))
    		throw new SessionException('Session file path is not writable', 1);
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
    		
    	return file_put_contents(self::SESSION_FILE, (array)json_encode($accessData));
    }

    public function init()
    {
    	if ($accessData = @file_get_contents(self::SESSION_FILE))
    		return $this->start((array)json_decode($accessData));
    	return false;
    }

    public function end()
    {
    	unset($this->accessToken, $this->refreshToken, $this->expiresTime);
    	return unlink(self::SESSION_FILE);
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