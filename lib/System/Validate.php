<?php

namespace WHMCS\Module\Addon\SoluteDNS\System;

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        Validate.php
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

use solutedns\Dns\Validate as DNS_Validate;

/**
 * Internal Validation Class
 */
class Validate {

	/**
	 * Validation request.
	 *
	 * @param string $content
	 * @param string $type
	 *
	 * @return bolean
	 */
	public function input($content, $type) {

		if ($type == 'comsep') {
			// 1,2,3
			$regex = '/(^\d+(?:,\d+)*$)/';
		}
		if ($type == 'license') {
			// Abc 123 - _
			$regex = '/(^[A-Za-z0-9\-\_]*$)/';
		}
		if ($type == 'check') {
			// 1 or on
			$regex = '/^(on|1)$/';
		}
		if ($type == 'hash') {
			// Hash
			$regex = '/(^[^<>\'"#]+$)/';
		}
		if ($type == 'default') {
			// Abc 123 - _
			$regex = '/(^[A-Za-z0-9\-\._ ]*$)/';
		}
		if ($type == 'default_ext') {
			// Abc 123 - _ <> / : ; .
			$regex = '/(^[A-Za-z0-9\-{}<>\/:;\._ ]*$)/';
		}
		if ($type == 'int') {
			// Integer
			$regex = '/(^[0-9-][0-9]*$)/';
		}
		if ($type == 'intl') {
			// Intiger 1 +
			$regex = '/(^[1-9][0-9]*$|^n$)/';
		}
		if ($type == 'zonetype') {
			// 1 or on
			$regex = '/^(MASTER|NATIVE)$/i';
		}
		if ($type == 'serial') {
			// 1 or on
			$regex = '/^(default|epoch|zero)$/i';
		}
		if ($type == 'domain') {
			// Abc 123 - _ .
			$regex = '/(^[A-Za-z0-9\-\._ ]*$)/';

			// IDN Format
			if (extension_loaded('intl')) {
				$content = idn_to_ascii($content);
			}
		}

		if ($type == 'domain_limited') {
			// Abc 123 - _ .
			$regex = '/(^[0-9a-z-]+\.(?:(?:co|or|gv|ac)\.)?[a-z]{2,7}$)/';

			// IDN Format
			if (extension_loaded('intl')) {
				$content = idn_to_ascii($content);
			}
		}

		if (is_null($regex)) {
			return 'invalid';
		} elseif (is_null($content) || empty($content)) {
			return true;
		} elseif (preg_match($regex, $content)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Template validator.
	 *
	 * @param array $input
	 *
	 * @return array
	 */
	public static function template($input) {

		$validate = new DNS_Validate();
		return $validate->pre($input);
	}

}
