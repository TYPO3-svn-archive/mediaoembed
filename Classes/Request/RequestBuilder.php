<?php
//declare(ENCODING = 'utf-8');

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
 * Builds a request object based on the (TypoScript) configuration
 *
 * @package mediaoembed
 * @subpackage Request
 * @version $Id:$
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class Tx_Mediaoembed_Request_RequestBuilder {

	/**
	 * TypoScript / Flexform configuration
	 *
	 * @var Tx_Mediaoembed_Content_Configuration
	 */
	protected $configuration;

	/**
	 * Request object that is build by this request builder
	 *
	 * @var Tx_Mediaoembed_Request_HtmlRequest
	 */
	protected $request;

	/**
	 * The provider for which the request will be created
	 *
	 * @var Tx_Mediaoembed_Request_Provider
	 */
	protected $provider;

	/**
	 * Array of possible endpoints for the current provider.
	 *
	 * @var array
	 */
	protected $endpoints;

	/**
	 * Injector for the TypoScript / Flexform configuration
	 *
	 * @param Tx_Mediaoembed_Content_Configuration $configuration
	 */
	public function injectConfiguration($configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Initializes the endpoints for the current provider.
	 *
	 * @param boolean $providerChanged if TRUE the endpoints array will be initialized with new endpoints from the current provider, otherwise the array pointer of the endpoints array will be moved forward.
	 * @return boolean TRUE if endpoints are available, otherwise FALSE
	 */
	protected function initializeEndpoints($providerChanged) {

		if ($providerChanged) {

			$this->endpoints = $this->provider->getAllEndpoints();

			if (!count($this->endpoints)) {
				throw new Tx_Mediaoembed_Exception_NoProviderEndpointException($this->provider);
			}

			reset($this->endpoints);

		} else {

			if (!next($this->endpoints)) {
				return FALSE;
			}
		}

		return TRUE;
	}

	/**
	 * Initializes the provider for which the request will be build
	 *
	 * @param Tx_Mediaoembed_Request_Provider $provider
	 * @return boolean TRUE if provider changes, otherwilse FALSE
	 */
	protected function initializeProvider($provider) {

		if ($provider->equals($this->provider)) {
			return FALSE;
		}

		$this->provider = $provider;
		return TRUE;
	}

	/**
	 * Builds a request using the given configuration and the
	 * given provider data.
	 *
	 * @param Tx_Mediaoembed_Request_Provider $provider
	 * @return Tx_Mediaoembed_Request_HtmlRequest or FALSE if no further requests are available
	 */
	public function buildNextRequest($provider) {
		$providerChanged = $this->initializeProvider($provider);

			// If provider has no further endpoints we return FALSE
		if (!$this->initializeEndpoints($providerChanged)) {
			return FALSE;
		}

		$this->initializeNewRequest();
		return $this->request;
	}

	/**
	 * Build a new request in the request property
	 *
	 * @return void
	 */
	protected function initializeNewRequest() {
		$this->request = t3lib_div::makeInstance('Tx_Mediaoembed_Request_HttpRequest');
		$this->request->injectConfiguration($this->configuration);
		$this->request->setEndpoint(current($this->endpoints));
	}
}
?>