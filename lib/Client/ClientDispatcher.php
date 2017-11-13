<?php

namespace WHMCS\Module\Addon\SoluteDNS\Client;

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        ClientDispatcher.php
 * @package     solutedns-ce-whmcs
 *
 * Copyright (c) 2017 NetDistrict
 * All rights reserved.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR AND CONTRIBUTORS ``AS IS'' AND ANY
 * EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL THE AUTHOR OR CONTRIBUTORS BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR
 * SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY
 * OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH
 * DAMAGE.
 *
 * @license     SoluteDNS - End User License Agreement, http://www.solutedns.com/eula/
 * @author      NetDistrict <info@netdistrict.net>
 * @copyright   NetDistrict
 * @link        https://www.solutedns.com
 * */
if (!defined("WHMCS")) {
	die("This file cannot be accessed directly");
}

/**
 * Client Area Dispatch Handler
 */
class ClientDispatcher {

	/**
	 * Dispatch request.
	 *
	 * @param string $action
	 * @param array $parameters
	 *
	 * @return array
	 */
	public function dispatch($action, $parameters) {
		if (!$action) {
			// Default to index if no action specified
			$action = 'index';
		}

		$controller = new Controller();

		// Verify requested action is valid and callable
		if (is_callable([$controller, $action])) {
			return $controller->$action($parameters);
		}
	}

}
