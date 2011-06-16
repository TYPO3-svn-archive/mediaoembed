<?php
/*                                                                        *
 * This script belongs to the TYPO3 extension "mediaoembed".              *
 *                                                                        *
 * It is free software; you can redistribute it and/or modify it under    *
 * the terms of the GNU General Public License as published by the Free   *
 * Software Foundation, either version 3 of the License, or (at your      *
 * option) any later version.                                             *
 *                                                                        *
 * This script is distributed in the hope that it will be useful, but     *
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHAN-    *
 * TABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General      *
 * Public License for more details.                                       *
 *                                                                        *
 * You should have received a copy of the GNU General Public License      *
 * along with the script.                                                 *
 * If not, see http://www.gnu.org/licenses/gpl.html                       *
 *                                                                        *
 * The TYPO3 project - inspiring people to share!                         *
 *                                                                        */

/**
 * @package mediaoembed
 * @subpackage Hooks
 * @version $Id:$
 */
class Tx_Mediaoembed_Hooks_TslibContentGetDataRegisterArray implements tslib_content_getDataHook {

	/**
	 * Extends the getData()-Method of tslib_cObj to process more/other commands
	 *
	 * @param	string		full content of getData-request e.g. "TSFE:id // field:title // field:uid"
	 * @param	array		current field-array
	 * @param	string		currently examined section value of the getData request e.g. "field:title"
	 * @param	string		current returnValue that was processed so far by getData
	 * @param	tslib_cObj	parent content object
	 * @return	string		get data result
	 */
	public function getDataExtension($getDataString, array $fields, $sectionValue, $returnValue, tslib_cObj &$parentObject) {

		$parts = explode(':', $sectionValue, 2);

		$key = trim($parts[1]);
		$type = strtolower(trim($parts[0]));

		if ((string) $type !== 'registerobj') {
			return $returnValue;
		}

		if (empty($key)) {
			return $returnValue;
		}

		$returnValue = $this->getResponseDataFromRegister($key, $GLOBALS['TSFE']->register);
		return $returnValue;
	}

	/**
	 * Return response data the input string $keyString defines array keys / getter methods separated by "|"
	 * Example: $var = "response|authorName" will return the value $data["reponse"]->getAuthorName() value
	 *
	 * @param string $keyString var key, eg. "response|authorName" to get the Parameter $data["reponse"]->getAuthorName() back.
	 * @param object|array $data the object or array where the data is read from
	 * @return mixed Whatever value. If none, then blank string.
	 * @see tslib_cObj::getGlobal()
	 */
	protected function getResponseDataFromRegister($keyString, $data) {
		$keys = t3lib_div::trimExplode('|', $keyString);
		$numberOfLevels = count($keys);
		$rootKey = $keys[0];
		$value = $this->getArrayOrObjectValue($rootKey, $data);

		for ($i = 1; $i < $numberOfLevels && isset($value); $i++) {
			$currentKey = trim($keys[$i]);
			$value = $this->getArrayOrObjectValue($currentKey, $value);
		}

		if (!is_scalar($value)) {
			$value = '';
		}
		return $value;
	}

	/**
	 * Tries to fetch a value from the $data object / array
	 * with the given $key. If $data is an object we try to call
	 * a getter function for the given $key. If $data is an array
	 * we try to read the $key from the $data array. If both fails,
	 * NULL will be returned.
	 *
	 * @param string $key
	 * @param object|array $data
	 */
	protected function getArrayOrObjectValue($key, $data) {

		if (is_object($data)) {
			$getter = 'get' . ucfirst($key);
			if (method_exists($data, $getter)) {
				return $data->$getter();
			}  else {
				throw new Exception(sprintf('Object %s did not have getter function %s', get_class($data), $getter));
			}
		} elseif (is_array($data)) {
			if (array_key_exists($key, $data)) {
				return $data[$key];
			} else {
				throw new Exception(sprintf('array key %s did not exist', $key));
			}
		} else {
			throw new Exception(sprintf('Current data was neither array nor object, key was: %s', $key));
		}

		return NULL;
	}
}

?>