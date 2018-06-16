<?php

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        
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
 * Required libraries and dependencies
 */
use WHMCS\Module\Addon\SoluteDNS\Admin\AdminDispatcher;
use WHMCS\Module\Addon\SoluteDNS\Client\ClientDispatcher;
use Illuminate\Database\Capsule\Manager as Capsule;

/**
 * Module Configueation.
 *
 * @return array
 */
function solutedns_config() {
	return [
		'name' => 'SoluteDNS - Community Edition',
		'description' => 'DNS Management for PowerDNS nameservers with MySQL back-end.',
		'author' => 'NetDistrict',
		'language' => 'english',
		'version' => '0.18.001',
	];
}

/**
 * Activate.
 *
 * Called upon activation of the module for the first time.
 *
 * @return array Optional success/failure message
 */
function solutedns_activate() {

	// Database
	$pdo = Capsule::connection()->getPdo();
	$pdo->beginTransaction();

	try {

		// Create Table: SETTINGS
		$query = "CREATE TABLE IF NOT EXISTS `mod_solutedns_settings` (
			  `id` int(5) NOT NULL AUTO_INCREMENT,
			  `setting` varchar(32) NOT NULL,
			  `value` text NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1 ;";

		$pdo->exec($query);

		$query = "ALTER TABLE mod_solutedns_settings ADD UNIQUE KEY (`setting`);";
		$pdo->exec($query);

		// Add Settings

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('eula', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('record_types', 'A,AAAA,CAA,CNAME,MX,NS,SRV,TXT,SSHFP,TLSA');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_hostmaster', 'hostmaster.{domain}');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_serial', 'default');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_refresh', '3600');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_retry', '600');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_expire', '604800');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('soa_ttl', '3600');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('custom_primary', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('record_limit', '0');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('respect_registrar', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('hide_soa', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('disable_ns', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('preset_ttl', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('dns_pagination', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('client_urlrewrite', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('auto_create', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('auto_delete', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('auto_delete_whmcs', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('auto_enabled', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('auto_todo', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('maintenance', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('logging', 'on');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('last_cron', '');";
		$pdo->exec($query);

		$query = "INSERT IGNORE INTO mod_solutedns_settings (setting,value) VALUES ('license', '');";
		$pdo->exec($query);

		// Create Table: NAMESERVERS
		$query = "CREATE TABLE IF NOT EXISTS `mod_solutedns_nameservers` (
			  `id` int(5) NOT NULL AUTO_INCREMENT,
			  `db_host` varchar(254) NOT NULL,
			  `db_port` int(5) NOT NULL,
			  `db_user` text NOT NULL,
			  `db_pass` text NOT NULL,
			  `db_name` text NOT NULL,
			  `zone_type` varchar(6) NOT NULL,
			  `ns0` varchar(254) NOT NULL,
			  `ns1` varchar(254) NOT NULL,
			  `ns2` varchar(254) NOT NULL,
			  `ns3` varchar(254) NOT NULL,
			  `ns4` varchar(254) NOT NULL,
			  `ns5` varchar(254) NOT NULL,
			  `ssh_host` varchar(254) NOT NULL,
			  `ssh_port` int(5) NOT NULL,
			  `ssh_user` text NOT NULL,
			  `ssh_pass` text NOT NULL,
			  `ssh_key` text NOT NULL,
			  `dnssec_enable` varchar(5) NOT NULL,
			  `dnssec_rectify` varchar(5) NOT NULL,
			  `dnssec_auto` varchar(5) NOT NULL,
			  `dnssec_nsec3` varchar(5) NOT NULL,
			  `dnssec_client` varchar(5) NOT NULL,
			  `version` int(2) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$pdo->exec($query);

		// Add nameserver
		$query = "INSERT INTO `mod_solutedns_nameservers` (dnssec_rectify, version) VALUES ('on', '3');";
		$pdo->exec($query);

		// Create Table: TEMPLATE RECORDS

		$query = "CREATE TABLE IF NOT EXISTS `mod_solutedns_template_records` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `product_id` int(10) NOT NULL,
			  `name` varchar(255) NOT NULL,
			  `type` varchar(6) NOT NULL,
			  `content` varchar(255) NOT NULL,
			  `ttl` int(10) NOT NULL,
			  `prio` int(10) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$pdo->exec($query);

		// Add Example Record
		$query = "INSERT IGNORE INTO mod_solutedns_template_records (product_id, name, type, content, ttl, prio) VALUES ('0', '{domain}', 'TXT', '\"Example Record\"', '3600', '0')";
		$pdo->exec($query);

		// Create Table: CRON QUEUE
		$query = "CREATE TABLE IF NOT EXISTS `mod_solutedns_cron_queue` (
			  `id` int(10) NOT NULL AUTO_INCREMENT,
			  `domain_id` int(10) NOT NULL,
			  `name` text NOT NULL,
			  `action` text NOT NULL,
			  `time` int(10) NOT NULL,
			  PRIMARY KEY (`id`),
			  UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$pdo->exec($query);

		// Create Table: CACHE
		$query = "CREATE TABLE IF NOT EXISTS `mod_solutedns_cache` (
			  `id` int(11) NOT NULL AUTO_INCREMENT,
			  `domain_id` int(11) NOT NULL,
			  `type` varchar(32) NOT NULL,
			  `content` text NOT NULL,
			  `time` varchar(11) NOT NULL,
			PRIMARY KEY (`id`),
			UNIQUE KEY `id` (`id`)
			) ENGINE=InnoDB  DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

		$pdo->exec($query);

		// Complete Activation
		$pdo->commit();

		return [
			'status' => 'success', // Supported values here include: success, error or info
			'description' => 'SoluteDNS for WHMCS - Community Edition has been activated successfully.',
		];
	} catch (Exception $e) {

		$pdo->rollBack();

		return [
			'status' => 'error', // Supported values here include: success, error or info
			'description' => 'An database error occured during activation: ' . $e->getMessage(),
		];
	}
}

/**
 * Deactivate.
 *
 * Called upon deactivation of the module.
 *
 * @return array Optional success/failure message
 */
function solutedns_deactivate() {

	// Database
	$pdo = Capsule::connection()->getPdo();
	$pdo->beginTransaction();

	try {

		// Remove Database Tables
		$query = "DROP TABLE IF EXISTS `mod_solutedns_cache`;";
		$pdo->exec($query);

		$query = "DROP TABLE IF EXISTS `mod_solutedns_cron_queue`;";
		$pdo->exec($query);

		$query = "DROP TABLE IF EXISTS `mod_solutedns_nameservers`;";
		$pdo->exec($query);

		$query = "DROP TABLE IF EXISTS `mod_solutedns_settings`;";
		$pdo->exec($query);

		$query = "DROP TABLE IF EXISTS `mod_solutedns_template_records`;";
		$pdo->exec($query);

		// Complete Deactivation
		$pdo->commit();

		return [
			'status' => 'success', // Supported values here include: success, error or info
			'description' => 'SoluteDNS for WHMCS - Community Edition has been deactivated successfully.',
		];
	} catch (Exception $e) {

		$pdo->rollBack();

		return [
			'status' => 'error', // Supported values here include: success, error or info
			'description' => 'An database error occured during deactivation: ' . $e->getMessage(),
		];
	}
}

/**
 * Upgrade.
 *
 * Called the first time the module is accessed following an update.
 *
 * @return void
 */
function solutedns_upgrade($vars) {
	$currentlyVersion = $vars['version'];
	
	if (version_compare($currentlyVersion, '0.18.001', '<')) {
		// No db changes
		$newVersion = 'v0.18.001';
	}
	
	###
	
	if ($error != true) {
		logActivity('SoluteDNS has been upgraded to: '. $newVersion .'.');	
	}
}

/**
 * Admin Area Output.
 *
 * Called when the addon module is accessed via the admin area.
 *
 * @see solutedns\Admin\Controller@index
 *
 * @return string
 */
function solutedns_output($vars) {
	// Get common module parameters
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$_lang = $vars['_lang'];
	$vars['base_path'] = dirname(__FILE__);

	// Dispatch and handle request here. What follows is a demonstration of one
	// possible way of handling this using a very basic dispatcher implementation.

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	$dispatcher = new AdminDispatcher();
	$response = $dispatcher->dispatch($action, $vars);
	echo $response;
}

/**
 * Admin Area Sidebar Output.
 *
 * Used to render output in the admin area sidebar.
 *
 * @param array $vars
 *
 * @return string
 */
function solutedns_sidebar($vars) {
	// Get common module parameters
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$_lang = $vars['_lang'];

	$sidebar = '<span class="header">
	<img class="absmiddle" src="images/icons/support.png" width="16" height="16">
	SoluteDNS
	</span>
	<ul class="menu">
		<li>
		<a href="https://docs.solutedns.com/whmcs/ce/" target="_blank">Documentation</a>
		</li>
		<li>
		<a href="https://forum.solutedns.com/" target="_blank">Support</a>
		</li>
		<li>
		<a href="https://docs.solutedns.com/general/report-an-issue/" target="_blank">Report a bug</a>
		</li>
	</ul>';

	return $sidebar;
}

/**
 * Client Area Output.
 *
 * Called when the addon module is accessed via the client area.
 *
 * @see solutedns\Client\Controller@index
 *
 * @return array
 */
function solutedns_clientarea($vars) {
	// Get common module parameters
	$modulelink = $vars['modulelink'];
	$version = $vars['version'];
	$_lang = $vars['_lang'];

	// Dispatch and handle request here. What follows is a demonstration of one
	// possible way of handling this using a very basic dispatcher implementation.

	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : '';

	$dispatcher = new ClientDispatcher();
	return $dispatcher->dispatch($action, $vars);
}
