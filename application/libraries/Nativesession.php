<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Nativesession
{
    public function __construct()
    {
        session_set_cookie_params(0);
        session_start();
    }

    public function set_userdata( $key, $value )
    {
        $_SESSION[$key] = $value;
    }

    public function userdata( $key )
    {
        return isset( $_SESSION[$key] ) ? $_SESSION[$key] : null;
    }

    public function regenerateId( $delOld = false )
    {
        session_regenerate_id( $delOld );
    }

    public function unset_userdata( $key )
    {
        unset( $_SESSION[$key] );
    }

    public function ses_destroy(){
        @session_unset();
        session_destroy();
    }

}


/* End of file Nativesession.php */
/* Location: ./application/libraries/Nativesession.php */