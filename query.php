<?php

define('SERVEROK',    0);
define('SERVERDOWN',    1);
define('SERVERINVALID',  2);
define('UNKNOWN_ERROR',  3);
define('TIMEOUT',        1);

function UT3QueryServer($host,$port,&$return)
{
    $sock = @fsockopen( "udp://" . $host, $port );
    if ( !$sock ) return SERVERINVALID;
    socket_set_timeout( $sock, 0, 500000 );
    if ( !@fwrite( $sock, "\xFE\xFD\x09\x10\x20\x30\x40\xFF\xFF\xFF\x01" ) )
        return SERVERDOWN;
    $challenge = fread( $sock, 1400 );
    if ( !$challenge )
        return UNKNOWN_ERROR;
    $challenge = substr( preg_replace( "/[^0-9\-]/si", "", $challenge ), 1 );
    $query = sprintf(
        "\xFE\xFD\x00\x10\x20\x30\x40%c%c%c%c\xFF\xFF\xFF\x01",
        ( $challenge >> 24 ),
        ( $challenge >> 16 ),
        ( $challenge >> 8 ),
        ( $challenge >> 0 )
    );
    if ( !@fwrite( $sock, $query ) )
        return UNKNOWN_ERROR;
    $response = array();
    for ($x = 0; $x < 2; $x++)
    {
        $response[] = @fread($sock,2048);
    }
    $response = implode($response);
    $response = substr($response,16);
    $response = explode("\0",$response);
    array_pop($response);
    array_pop($response);
    array_pop($response);
    array_pop($response);
    $return = array();
    $type = 0;
    foreach ($response as $key)
    {
        if ($type == 0) $val = $key;
        if ($type == 1) $return[$val] = $key;

        $type == 0 ? $type = 1 : $type = 0;
    }
    return SERVEROK;
}
$host = SERVER_HOST;
$port = SERVER_PORT;
if (empty($_GET['port'])) {
    $port = 19132;
}
$values = '';
$returnvalue = UT3QueryServer($host,$port,$values);

if($returnvalue != SERVEROK){
    echo '<p class="text-danger text-center">Server is down.</p>';
}else{
    $currentplayers = (int) $values['numplayers'];
    $maxplayers    = (int) $values['maxplayers'];
    $version    = $values['version'];
    $engine = $values['server_engine'];
    $hostname      = $values['hostname'];
    $percent = $currentplayers / $maxplayers*100;
    $ostatus = 1;

}
