<?php

namespace WHMCS\Module\Addon\SoluteDNS\System;

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        Cron.php
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

use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\SoluteDNS\Admin\Controller as DNS_Controller;
use solutedns\Dns\Dnssec;

/**
 * Cronjob Handler
 */
class Cron {

	private $dnssec = NULL;
	private $session = NULL;

	public function run() {

		// Create Session
		$this->dnssec = new Dnssec();
		$this->session = $this->dnssec->session();

		// Handle Tasks
		foreach (Capsule::table('mod_solutedns_cron_queue')->get() as $queue) {

			if ($queue->action == 'rectify') {

				$this->rectify($queue->name);
			}
		}

		// Update Last Run
		$time = time();
		Capsule::table('mod_solutedns_settings')->where('setting', 'last_cron')->update(['value' => $time]);
	}

	private function rectify($domain) {

		$result = $this->dnssec->rectify($domain, $this->session);

		if (isset($result['error']) && $result['error']['code'] != '3001') {

			$error = $result['error'];
			if (DNS_Controller::Config('logging')) {
				logActivity('DNS CRON ERROR [' . $error['code'] . '] for ' . $domain . ': ' . $error['desc'], 0);
			}
		} else {

			try {

				Capsule::table('mod_solutedns_cron_queue')->where('name', $domain)->where('action', 'rectify')->delete();
			} catch (\Exception $e) {

				if (DNS_Controller::Config('logging')) {
					logActivity('DNS CRON ERROR [DB] for ' . $domain . ': ' . $e->getMessage(), 0);
				}
			}
		}
	}

}
