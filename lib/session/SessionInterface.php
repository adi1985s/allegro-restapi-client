<?php

/**
 * @author Ireneusz Kierkowski <ircykk@gmail.com>
 * @copyright 2016 Ireneusz Kierkowski <https://github.com/ircykk>
 * @license http://www.opensource.org/licenses/MIT MIT License
 */
interface SessionInterface
{	
    public function start($OauthCode);
    public function init();
    public function end();
}