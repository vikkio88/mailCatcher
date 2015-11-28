<?php
/**
 * Created by PhpStorm.
 * User: vincenzo.ciaccio
 * Date: 05/11/2015
 * Time: 12:05
 */

namespace App\Lib\Helpers;


/**
 * Class Logger
 * @package App\Lib\Helpers
 */
class Logger
{
	/**
	 * @param $stuff
	 */
	public static function write($stuff){
		if(is_array($stuff)){
			$stuff = print_r($stuff,true);
		}
		file_put_contents(
				Config::get('config.logFileName'),
				date('d/m/Y h:i:s a', time())
				.": ".
				$stuff.
				"\n",
				FILE_APPEND);
	}

}