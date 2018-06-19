<?php

namespace WHMCS\Module\Addon\SoluteDNS\Client;

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        Client/Controller.php
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

use WHMCS\ClientArea;
use WHMCS\Database\Capsule;
use WHMCS\Module\Addon\SoluteDNS\System\Validate;
use WHMCS\Module\Addon\SoluteDNS\Vendor\DataTables\SSP;
use solutedns\System\Core;
use solutedns\System\db;
use solutedns\Dns\Records;
use solutedns\Dns\Dnssec;
use solutedns\Dns\Zones;

/**
 * Client Area Controller
 */
class Controller {

	/**
	 * Index action.
	 *
	 * @param array $vars Module configuration parameters
	 *
	 * @return array
	 */
	public function index($vars) {
		// Get common module parameters
		$modulelink = $vars['modulelink'];
		$version = $vars['version'];
		$LANG = $vars['_lang'];

		// Domain ID
		$domain_id = (int) filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

		// Get Client Details
		$ca = new ClientArea();

		if ($ca->isLoggedIn()) {

			$user_id = $ca->getUserID();
			$domain = $this->getDomain($domain_id, $user_id);
			$this->user_id = $user_id;
		}

		// Get DNSsec Keys
		$keys = NULL;

		// Get System State
		$maintenance = $this->config('maintenance');

		if (!empty($this->ns_details('dnssec_enable')) && empty($maintenance)) {

			$time = time();

			$intl = Capsule::table('mod_solutedns_cache')->where('domain_id', $domain_id)->first();

			if (is_null($intl)) {

				$zones = new Zones();

				$exists = $zones->exists($domain->domain);

				if ($exists == true) {

					$dnssec = new Dnssec();

					$result = $dnssec->get($domain->domain);

					if ($result['keys'] == NULL && $result['ds'] == NULL && $result['nsec'] == NULL) {

						$keys = '';

						Capsule::table('mod_solutedns_cache')->insert([
							'domain_id' => $domain_id,
							'type' => 'dnssec',
							'content' => $keys,
							'time' => $time,
						]);
					} else {

						$keys = serialize($result);

						Capsule::table('mod_solutedns_cache')->insert([
							'domain_id' => $domain_id,
							'type' => 'dnssec',
							'content' => $keys,
							'time' => $time,
						]);

						$keys = $result;
					}
				}
			} else {
				$keys = unserialize($intl->content);
			}
		}

		return ['pagetitle' => \Lang::trans('domaindnsmanagement'),
			'breadcrumb' => [
				'clientarea.php' => \Lang::trans('clientareatitle'),
				'clientarea.php?action=domains' => \Lang::trans('clientareanavdomains'),
				'clientarea.php?action=domaindetails&id=' . $domain_id => isset($domain->domain) ? $domain->domain : 'N/A',
				'index.php?m=solutedns&id=' . $domain_id => \Lang::trans('domaindnsmanagement'),
			],
			'templatefile' => 'client_manage',
			'requirelogin' => true, // Set true to restrict access to authenticated client users
			'vars' => [
				'modulelink' => $modulelink,
				'MLANG' => $LANG,
				'domain' => $domain,
				'dnssec' => $keys,
				'records' => $this->inConfig('record_types'),
				'preset_ttl' => $this->config('preset_ttl'),
				'maintenance' => $maintenance,
			],
		];
	}

	/**
	 * Post action.
	 *
	 * @return json
	 */
	public function post($vars) {

		// Get common module parameters		
		$this->lang = $vars['_lang']; // an array of the currently loaded language variables
		
		// Get posted data
		$data = isset($_REQUEST['data']) ? json_decode(html_entity_decode(filter_input(INPUT_POST, "data", FILTER_SANITIZE_SPECIAL_CHARS)), true) : '';
		if($data == NULL) {
			$data = isset($_REQUEST['data']) ? json_decode(html_entity_decode(filter_input(INPUT_GET, "data", FILTER_SANITIZE_SPECIAL_CHARS)), true) : '';	
		}

		// Set form action if action is not set
		if (!isset($data['action']) && isset($data[0]['name']) && $data[0]['name'] == 'sdns_form') {
			$data['action'] = $data[0]['value'];
		}

		/**
		 * Action: Get System State
		 */
		if ($data['action'] == 'systemState') {

			if ($this->config('maintenance')) {

				$arr[] = [
					'status' => 'default',
					'title' => $this->lang['client_msg_maintenance_title'],
					'msg' => $this->lang['client_msg_maintenance_desc'],
					'fixed' => true,
				];
			}

			$core_data = $this->core();

			if ($core_data != NULL) {

				$license_data = $core_data['license'];

				if ($license_data['status'] == 'database') {

					// Nameserver unavailable
					$arr[] = [
						'status' => 'error',
						'title' => $this->lang['client_msg_system_error_title'],
						'msg' => $this->lang['client_msg_system_error_desc'],
						'fixed' => true,
					];
				}

				if ($license_data['status'] != 'database' && isset($data['zone'])) {

					$zones = new Zones();

					$exists = $zones->exists($data['zone']);

					if ($exists == false) {
						$arr[] = [
							'status' => 'warning',
							'title' => $this->lang['global_msg_dns_nozone_title'],
							'msg' => $this->lang['global_msg_dns_nozone_desc'],
							'fixed' => true,
						];
					}

					$slave = $zones->slave($data['zone']);

					if ($slave == true) {
						$arr[] = [
							'status' => 'warning',
							'title' => $this->lang['global_msg_dns_slave_title'],
							'msg' => $this->lang['global_msg_dns_slave_desc'],
							'fixed' => true,
						];
					}
				}
			} else {

				$arr[] = [
					'status' => 'error',
					'title' => $this->lang['client_msg_system_error_title'],
					'msg' => $this->lang['client_msg_system_error_desc'],
					'fixed' => true,
				];
			}

			echo json_encode($arr);
			exit();
		}

		// Client Check
		$ca = new ClientArea();

		if ($ca->isLoggedIn()) {

			$user_id = $ca->getUserID();
			$getDomain = $this->getDomain((int) $data['zone'], $user_id);

			if ($getDomain == false) {

				$arr[] = [
					'status' => 'error',
					'title' => $this->lang['client_msg_access_denied_title'],
					'msg' => $this->lang['client_msg_access_denied_desc'],
				];

				echo json_encode($arr);
				exit();
			}
		}

		// Maintenance check
		$this->maintenance = empty($this->config('maintenance')) ? false : true;

		if ($this->maintenance) {

			$arr[] = [
				'status' => 'error',
				'title' => $this->lang['client_msg_system_error_title'],
				'msg' => $this->lang['client_msg_system_error_desc'],
			];

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Add Record
		 */
		if ($data['action'] == 'addrecord') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $getDomain->domain;
				$send['records'][] = [
					'name' => $data['name'],
					'type' => $data['type'],
					'content' => $data['content'],
					'ttl' => $data['ttl'],
					'prio' => $data['prio']
				];

				$records = new Records();
				$result = $records->add($send);

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($getDomain->domain, 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $this->lang['global_msg_dns_record_added_title'],
						'msg' => $this->lang['global_msg_dns_record_added_desc'],
						'fieldreset' => true,
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'error',
							'title' => $this->lang['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $this->lang['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
						'fieldreset' => true,
						'tablereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Edit Record
		 */
		if ($data['action'] == 'editrecord') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $getDomain->domain;
				$send['records'][] = [
					'id' => $data['record_id'],
					'name' => $data['name'],
					'type' => $data['type'],
					'content' => $data['content'],
					'ttl' => $data['ttl'],
					'prio' => $data['prio']
				];

				$records = new Records();
				$result = $records->edit($send);

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($getDomain->domain, 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $this->lang['global_msg_dns_record_edited_title'],
						'msg' => $this->lang['global_msg_dns_record_edited_desc'],
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'error',
							'title' => $this->lang['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
							'id' => $error['record']['id'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $this->lang['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
						'fieldreset' => true,
						'tablereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Delete Record
		 */
		if ($data['action'] == 'deleterecord') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $getDomain->domain;
				$send['records'][] = [
					'id' => $data['record_id']
				];

				$records = new Records();
				$result = $records->delete($send);

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($getDomain->domain, 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $this->lang['global_msg_dns_record_deleted_title'],
						'msg' => $this->lang['global_msg_dns_record_deleted_desc'],
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'error',
							'title' => $this->lang['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
							'id' => $error['record']['id'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $this->lang['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
						'fieldreset' => true,
						'tablereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Delete Selected Records
		 */
		if ($data['action'] == 'deleteselectedrecords') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $getDomain->domain;
				$send['records'] = [];
				$i = 0;

				foreach ($data['records'] as $record_id) {

					if (!filter_var($record_id, FILTER_VALIDATE_INT)) {
						continue;
					}

					$send['records'][] = [
						'id' => $record_id
					];

					$i++;
				}

				if ($i > 0) {

					$records = new Records();
					$result = $records->delete($send);

					if (isset($result['success'])) {

						if ($this->ns_details('dnssec_rectify')) {
							$this->add_crontask($getDomain->domain, 'rectify');
						}

						$arr[] = [
							'status' => 'success',
							'title' => sprintf($this->lang['global_msg_dns_record_select_deleted_title'], $i),
							'msg' => $this->lang['global_msg_dns_record_select_deleted_desc'],
							'tablereload' => true
						];
					}

					if (isset($result['errors'])) {

						foreach ($result['errors'] as $error) {

							$arr[] = [
								'status' => 'error',
								'title' => $this->lang['global_msg_dns_record_error_title'],
								'msg' => $this->getErrorMsg($error),
								'field' => $error['field'],
								'id' => $error['record']['id'],
								'tablereload' => true
							];
						}
					}

					if (isset($result['error'])) {

						$arr[] = [
							'status' => 'error',
							'title' => $this->lang['global_msg_dns_error_occurred'],
							'msg' => $this->getErrorMsg($result['error']),
							'tablereload' => true
						];
					}
				} else {
					$arr[] = [
						'status' => 'error',
						'title' => $this->lang['global_msg_dns_record_select_no_deleted_title'],
						'msg' => $this->lang['global_msg_dns_record_select_no_deleted_desc'],
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Invalid Action
		 */
		$arr[] = [
			'status' => '',
			'title' => $this->lang['global_msg_invalid_request_title'],
			'msg' => $this->lang['global_msg_invalid_request_desc'],
		];

		echo json_encode($arr);
		exit();
	}

	/**
	 * GET action.
	 */
	public function get($vars) {

		// Set headers
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');

		// Set language variable
		$this->lang = $vars['_lang'];

		if (isset($_GET['table'])) {

			$domain_id = isset($_GET['data']) ? (int) filter_input(INPUT_GET, "data", FILTER_SANITIZE_NUMBER_INT) : 0;

			// Configuration
			$table = 'records';
			$primaryKey = 'id';

			// Get Client Details
			$ca = new ClientArea();

			if ($ca->isLoggedIn()) {

				$user_id = $ca->getUserID();
				$getDomain = $this->getDomain($domain_id, $user_id);

				if ($getDomain == false) {

					SSP::fatal(
							$this->lang['client_msg_access_denied_desc']
					);
				}
			}

			// Get System State
			$this->maintenance = empty($this->config('maintenance')) ? NULL : 'DISABLED';
			
			// IDN formatting
			$domain = $this->idn($getDomain->domain);

			// Get Remote ID	
			try {

				$db = db::get();
				$stmt = $db->prepare("SELECT id FROM domains where name = :domain");
				$stmt->execute([':domain' => $domain]);
				$row = $stmt->fetch(\PDO::FETCH_ASSOC);

				$domain_id = $row['id'];
			} catch (\ErrorException $e) {
				echo '{"draw":1,"recordsTotal":0,"recordsFiltered":0,"data":[]}';
				exit();
			}

			// Set database
			$ns_details = [
				'type' => 'remote',
			];

			// Columns
			$columns = [
				[
					'db' => 'id', 'dt' => 0,
					'formatter' => function( $d, $row ) {
						$option = ($row['type'] == 'NS') ? empty($this->config('disable_ns')) ? NULL : " DISABLED" : NULL;
						$option = ($row['type'] == 'SOA') ? " DISABLED" : $option;
						return '<div class="checkbox tablecheckbox"><input type="checkbox" name="sdns_select" id="sdns_select_' . $row['id'] . '" value="' . $row['id'] . '" style="display: hidden;" ' . $this->maintenance . $option . '/><label for="sdns_select_' . $row['id'] . '" /></div>';
					}
				],
				[
					'db' => 'name', 'dt' => 1,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-name_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_name_' . $row['id'] . '" id="sdns_name_' . $row['id'] . '" value="' . $row['name'] . '"></div>';
					}
				],
				[
					'db' => 'type', 'dt' => 2,
					'formatter' => function( $d, $row ) {

						$record_types = $this->inConfig('record_types');

						foreach ($record_types as $record_type) {

							$selected = ($row['type'] == $record_type) ? 'SELECTED ' : NULL;
							$allowed_types[] = '<option ' . $selected . 'value="' . $record_type . '">' . $record_type . '</option>';
						}

						if (!in_array($row['type'], $record_types)) {
							$allowed_types[] = '<option SELECTED value="' . $row['type'] . '">' . $row['type'] . '</option>';
						}

						$allowed_types = implode("\r\n", $allowed_types);

						return '<select DISABLED class="form-padding form-control dnsfield" name="sdns_type_' . $row['id'] . '" id="sdns_type_' . $row['id'] . '">
						' . $allowed_types . '
					</select>';
					}
				],
				[
					'db' => 'content', 'dt' => 3,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-content_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_content_' . $row['id'] . '" id="sdns_content_' . $row['id'] . '" value="' . htmlentities($row['content']) . '"></div>';
					}
				],
				[
					'db' => 'prio', 'dt' => 4,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-prio_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield text-center" name="sdns_prio_' . $row['id'] . '" id="sdns_prio_' . $row['id'] . '" value="' . $row['prio'] . '"></div>';
					}
				],
				[
					'db' => 'ttl', 'dt' => 5,
					'formatter' => function( $d, $row ) {

						$preset_ttl = $this->config('preset_ttl');
						if ($preset_ttl == 'on' || $preset_ttl == '1') {

							$selected = [
								'60' => NULL,
								'300' => NULL,
								'3600' => NULL,
								'86400' => NULL
							];

							$selected[$row['ttl']] = 'SELECTED';

							if ($row['ttl'] == '60' || $row['ttl'] == '300' || $row['ttl'] == '3600' || $row['ttl'] == '86400') {
								$custom_ttl = NULL;
							} else {
								$custom_ttl = '<option SELECTED value="' . $row['ttl'] . '">' . $row['ttl'] . '</option>';
							}

							return '<select DISABLED class="form-padding form-control dnsfield" name="sdns_ttl_' . $row['id'] . '" id="sdns_ttl_' . $row['id'] . '">
                    <option ' . $selected['60'] . ' value="60">1 ' . $this->lang['global_dns_minute'] . '</option>
                    <option ' . $selected['300'] . ' value="300">5 ' . $this->lang['global_dns_minutes'] . '</option>
                    <option ' . $selected['3600'] . ' value="3600">1 ' . $this->lang['global_dns_hour'] . '</option>
                    <option ' . $selected['86400'] . ' value="86400">1 ' . $this->lang['global_dns_day'] . '</option>
					' . $custom_ttl . '
				</select>';
						} else {
							return '<input DISABLED type="textbox" class="form-padding form-control dnsfield text-center" name="sdns_ttl_' . $row['id'] . '" id="sdns_ttl_' . $row['id'] . '" value="' . $row['ttl'] . '">';
						}
					}
				],
				[
					'db' => 'id', 'dt' => 6,
					'formatter' => function( $d, $row ) {
						$option = ($row['type'] == 'NS') ? empty($this->config('disable_ns')) ? NULL : " DISABLED" : NULL;
						$option = ($row['type'] == 'SOA') ? " DISABLED" : $option;
						return '<div class="text-center text-nowrap"><button type="button" class="btn btn-sm btn-success" style="display: none;" id="sdns_save_' . $row['id'] . '" onclick="record_edit(\'' . $row['id'] . '\')" ' . $this->maintenance . $option . '><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-warning" id="sdns_edit_' . $row['id'] . '" onclick="edit(\'' . $row['id'] . '\')" ' . $this->maintenance . $option . '><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#dialog_deleteRecord" onclick="setRecord(\'' . $row['id'] . '\')"  ' . $this->maintenance . $option . '><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div>';
					}
				]
			];

			// Where condition
			$option = empty($this->config('hide_soa')) ? NULL : " AND NOT (type = 'SOA')";
			$where = "domain_id = '$domain_id' AND NOT (type = '')" . $option;

			echo json_encode(
					SSP::complex($_GET, $ns_details, $table, $primaryKey, $columns, $where, NULL)
			);
		}

		exit();
	}

	/**
	 * Add task to cron queue.
	 */
	private function add_crontask($domain, $task) {

		// Check if task already exists

		$count = Capsule::table('mod_solutedns_cron_queue')->where('name', $domain)->where('action', $task)->count();

		if ($count < 1) {

			// Create task
			$time = time();

			Capsule::table('mod_solutedns_cron_queue')->insert(
					[
						'name' => $domain,
						'action' => $task,
						'time' => $time,
					]
			);
		}
	}

	/**
	 * Get configuration value.
	 */
	public static function config($setting) {

		return Capsule::table('mod_solutedns_settings')->select('value')->where('setting', $setting)->first()->value;
	}

	/**
	 * Get configuration value as array.
	 */
	public static function inConfig($setting) {

		return explode(',', Capsule::table('mod_solutedns_settings')->select('value')->where('setting', $setting)->first()->value);
	}

	/**
	 * Get nameserver settings.
	 */
	public function ns_details($setting) {

		return Capsule::table('mod_solutedns_nameservers')->select($setting)->where('id', '1')->first()->$setting;
	}

	/**
	 * Set/Detect SoluteDNS Core.
	 */
	public static function core() {

		if (class_exists('solutedns\System\Core')) {

			$core = new Core();

			$data['version'] = $core->version;
			$data['license'] = $core->license();

			if (isset($data['license']["configoptions"])) {
				$configoptions = explode("|", $data['license']["configoptions"]);

				$data['license']['zonelimit'] = NULL;

				foreach ($configoptions as $option) {

					if (strpos($option, 'Zones=') !== false) {
						$option = str_replace('Zones=', '', $option);
						$data['license']['zonelimit'] = $option;
					}
				}
			}

			if (!isset($data['license']["addons"])) {
				$data['license']["addon"] = false;
			} else {
				$addons = explode("|", $data['license']["addons"]);

				foreach ($addons as $value) {
					$addon = explode(";", $value);

					$data['license']["addon"][] = [
						'name' => str_replace('name=', '', $addon[0]),
						'status' => str_replace('status=', '', $addon[2]),
						'duedate' => str_replace('nextduedate=', '', $addon[1]),
					];
				}
			}

			return $data;
		} else {

			// Core not detected
			return NULL;
		}
	}

	/**
	 * IDN formatting
	 */
	public static function idn($domain) {
		
		if (extension_loaded('intl')) {
			return mb_strtolower(idn_to_ascii($domain, 0, INTL_IDNA_VARIANT_UTS46));
		} else {
			return mb_strtolower(utf8_encode($domain));
		}
	}

	/**
	 * Get domain name.
	 */
	public function getDomain($domain_id, $user_id) {

		if (is_int($domain_id) && is_int($user_id)) {
			return Capsule::table('tbldomains')->select('id', 'domain')->where('id', $domain_id)->where('userid', $user_id)->first();
		} else {
			return false;
		}
	}

	/**
	 * Convert error message
	 */
	public function getErrorMsg($error) {

		if (is_numeric($error['code'])) {
			if ((int)$error['code'] > 4900) {
				
				return $this->lang['global_msg_dns_error_occurred_desc'];
			}
			if ($error['code'] == '6004') {

				return $error['desc'];
			} else {

				$code = 'global_error_' . $error['code'];
				return $this->lang[$code];
			}
		} else {

			$code = 'global_validation_' . $error['code'];
			$desc = $this->lang[$code];
			$part = isset($error['part']) ? $error['part'] : NULL;

			return sprintf($desc, $part);
		}
	}

}
