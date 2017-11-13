<?php

/**
 *                           *** SoluteDNS Core ***
 *
 * @file        Configuration File
 * @package     solutedns
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

use WHMCS\Module\Addon\SoluteDNS\Admin\Controller;

$clr = new Controller();

// Create Nameserver Array
if ($clr->ns_details('ns0') !== '') {
	$nameservers[] = $clr->ns_details('ns0');
}
if ($clr->ns_details('ns1') !== '') {
	$nameservers[] = $clr->ns_details('ns1');
}
if ($clr->ns_details('ns2') !== '') {
	$nameservers[] = $clr->ns_details('ns2');
}
if ($clr->ns_details('ns3') !== '') {
	$nameservers[] = $clr->ns_details('ns3');
}
if ($clr->ns_details('ns4') !== '') {
	$nameservers[] = $clr->ns_details('ns4');
}
if ($clr->ns_details('ns5') !== '') {
	$nameservers[] = $clr->ns_details('ns5');
}

// Configuion
return [
	'license' => $clr->config('license'),
	'database' => [
		'host' => $clr->ns_details('db_host'),
		'port' => $clr->ns_details('db_port'),
		'name' => $clr->ns_details('db_name'),
		'user' => $clr->ns_details('db_user'),
		'pass' => $clr->decrypt($clr->ns_details('db_pass')),
		'type' => $clr->ns_details('zone_type') # native/master
	],
	'ssh' => [
		'host' => $clr->ns_details('ssh_host'),
		'port' => $clr->ns_details('ssh_port'),
		'user' => $clr->ns_details('ssh_user'),
		'pass' => $clr->decrypt($clr->ns_details('ssh_pass')),
		'private_key' => $clr->ns_details('ssh_key'),
		'powerdns_version' => $clr->ns_details('version') # 3/4
	],
	'records' => [
		'allowed' => $clr->config('record_types'),
		'limit' => $clr->config('record_limit'),
		'soa' => [
			'hostmaster' => str_replace(['{', '}'], ':', $clr->config('soa_hostmaster')),
			'serial' => $clr->config('soa_serial'), # default/epoch/last
			'refresh' => $clr->config('soa_refresh'),
			'retry' => $clr->config('soa_retry'),
			'expire' => $clr->config('soa_expire'),
			'ttl' => $clr->config('soa_ttl')
		],
		'custom_primary' => ($clr->config('custom_primary') !== '' ? true : false),
	],
	'nameservers' => $nameservers,
	'dnssec' => [
		'enabled' => ($clr->ns_details('dnssec_enable') !== '' ? true : false),
		'auto_rectify' => false,
		'auto_keys' => ($clr->ns_details('dnssec_auto') !== '' ? true : false),
		'auto_nsec3' => ($clr->ns_details('dnssec_nsec3') !== '' ? true : false),
	],
	'health' => [
		'self_check' => false,
		'consistency_check' => false,
		'record_check' => false,
	],
	'system' => [
		'debug' => false,
		'regex' => [
		//'VALIDATE_IPV4' => '#[\s\S]#',
		//'VALIDATE_IPV6' => '',
		//'VALIDATE_FQHN' => '',
		//'VALIDATE_FQDN' => '',
		//'VALIDATE_TYPES' => '',
		//'VALIDATE_QUOTED' => ''
		],
	],
];
