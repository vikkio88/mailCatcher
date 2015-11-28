<?php
/**
 * Created by PhpStorm.
 * User: vincenzo.ciaccio
 * Date: 05/11/2015
 * Time: 12:02
 */

namespace App\Lib\Helpers;


/**
 * Class FakeBrowser
 * @package App\Lib\Helpers
 */
class FakeBrowser
{
    /**
     * @param $url
     * @return string
     */
    public static function get($url)
    {
        $options = array(
            'http' => array(
                'method' => "GET",
                'header' => "Accept-language: en\r\n" .
                    "Cookie: foo=bar\r\n" .
                    "User-Agent: Mozilla/5.0 (iPad; U; CPU OS 3_2 like Mac OS X; en-us) AppleWebKit/531.21.10 (KHTML, like Gecko) Version/4.0.4 Mobile/7B334b Safari/531.21.102011-10-16 20:23:10\r\n"
            )
        );


        $context = stream_context_create($options);

        $html = file_get_contents($url, null, $context);
        if ($http_response_header[0] == "HTTP/1.1 200 OK") {
            return $html;
        } else {
            return null;
        }

    }

}