<?php
/* SVN FILE: $Id: number.php 5422 2007-07-09 05:23:06Z phpnut $ */

/**
 * Number Helper.
 *
 * Methods to make numbers more readable.
 *
 * PHP versions 4 and 5
 *
 * CakePHP(tm) :  Rapid Development Framework <http://www.cakephp.org/>
 * Copyright 2005-2007, Cake Software Foundation, Inc.
 *								1785 E. Sahara Avenue, Suite 490-204
 *								Las Vegas, Nevada 89104
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @filesource
 * @copyright		Copyright 2005-2007, Cake Software Foundation, Inc.
 * @link				http://www.cakefoundation.org/projects/info/cakephp CakePHP(tm) Project
 * @package			cake
 * @subpackage		cake.cake.libs.view.helpers
 * @since			CakePHP(tm) v 0.10.0.1076
 * @version			$Revision: 5422 $
 * @modifiedby		$LastChangedBy: phpnut $
 * @lastmodified	$Date: 2007-07-09 00:23:06 -0500 (Mon, 09 Jul 2007) $
 * @license			http://www.opensource.org/licenses/mit-license.php The MIT License
 */

/**
 * Number helper library.
 *
 * Methods to make numbers more readable.
 *
 * @package 	cake
 * @subpackage	cake.cake.libs.view.helpers
 */
class NumberHelper extends AppHelper {
/**
 * Formats a number with a level of precision.
 *
 * @param  float	$number	A floating point number.
 * @param  integer $precision The precision of the returned number.
 * @return float Enter description here...
 * @static
 */
	function precision($number, $precision = 3) {
		return sprintf("%01.{$precision}f", $number);
	}

/**
 * Returns a formatted-for-humans file size.
 *
 * @param integer $length Size in bytes
 * @return string Human readable size
 * @static
 */
	function toReadableSize($size) {
		switch($size) {
			case 0:
				return '0 Bytes';
			case 1:
				return '1 Byte';
			case $size < 1024:
				return $size . ' Bytes';
			case $size < 1024 * 1024:
				return $this->precision($size / 1024, 0) . ' KB';
			case $size < 1024 * 1024 * 1024:
				return $this->precision($size / 1024 / 1024, 2) . ' MB';
			case $size < 1024 * 1024 * 1024 * 1024:
				return $this->precision($size / 1024 / 1024 / 1024, 2) . ' GB';
			case $size < 1024 * 1024 * 1024 * 1024 * 1024:
				return $this->precision($size / 1024 / 1024 / 1024 / 1024, 2) . ' TB';
		}
	}
/**
 * Formats a number into a percentage string.
 *
 * @param float $number A floating point number
 * @param integer $precision The precision of the returned number
 * @return string Percentage string
 * @static
 */
	function toPercentage($number, $precision = 2) {
		return $this->precision($number, $precision) . '%';
	}
/**
 * Formats a number into a currnecy format.
 *
 * @param float $number A floating point number
 * @param integer $options if int then places, if string then before, if (,.-) then use it
 * 							or array with places and before keys
 * @return string formatted number
 * @static
 */
	function format($number, $options = false) {
		$places = 0;
		if (is_int($options)) {
			$places = $options;
		}

		$seperators = array(',', '.', '-', ':');

		$before = null;
		if (is_string($options) && !in_array( $options, $seperators)) {
			$before = $options;
		}
		$seperator = ',';
		if (!is_array($options) && in_array( $options, $seperators)) {
			$seperator = $options;
		}
		$decimals = '.';
		if(!is_array($options) && in_array( $options, $seperators)) {
			$decimals = $options;
		}
		$escape = true;
		if (is_array($options) && isset($options['escape'])) {
			$escape = $options['escape'];
		}

		if (is_array($options)) {
			if (isset($options['places'])) {
				$places = $options['places'];
				unset($options['places']);
			}

			if (isset($options['before'])) {
				$before = $options['before'];
				unset($options['before']);
			}

			if(isset($options['decimals'])) {
				$decimals = $options['decimals'];
				unset($options['decimals']);
			}

			if (isset($options['seperator'])) {
				$seperator = $options['seperator'];
				unset($options['seperator']);
			}
		}

		if ($escape) {
			$before = h($before);
		}

		return $before . number_format ($number, $places, $decimals, $seperator);
	}
/**
 * Formats a number into a currency format.
 *
 * @param float $number A floating point number
 * @param integer $precision The precision of the returned number
 * @return string Percentage string
 * @static
 */
	function currency ($number, $currency = 'USD') {

		switch ($currency) {
			case "EUR":
				return $this->format($number, array('escape' => false, 'places'=>'2', 'before'=>'&#8364;', 'seperator'=>'.', 'decimals'=>','));
			break;
			case "GBP":
				return $this->format($number, array('escape' => false, 'places'=>'2', 'before'=>'&#163;'));
			break;
			case 'USD':
				return $this->format($number, array('places'=>'2', 'before'=>'$'));
			break;
			default:
				return $this->format($number, array('places'=>'2', 'before'=> $currency));
			break;
		}
	}
}

?>