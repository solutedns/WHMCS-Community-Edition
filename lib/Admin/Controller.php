<?php

namespace WHMCS\Module\Addon\SoluteDNS\Admin;

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        Admin/Controller.php
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

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Module\Addon\SoluteDNS\System\Validate;
use WHMCS\Module\Addon\SoluteDNS\Vendor\DataTables\SSP;
use phpseclib\Crypt\AES;
use solutedns\System\Core;
use solutedns\System\db;
use solutedns\Dns\Records;
use solutedns\Dns\Dnssec;
use solutedns\Dns\Zones;

/**
 * Admin Area Controller
 */
class Controller {

	/**
	 * Index action.
	 *
	 * @param array $vars Module configuration parameters
	 *
	 * @return string
	 */
	public function index($vars) {
		// Get common module parameters
		$modulelink = $vars['modulelink'];
		$version = $vars['version'];
		$LANG = $vars['_lang'];
		$base_path = $vars['base_path'];

		// Set Smarty for SoluteDNS
		$smarty = new \Smarty();

		$smarty->caching = false;
		$smarty->compile_dir = $GLOBALS['templates_compiledir'];

		$smarty->assign('base_path', $base_path);
		$smarty->assign('LANG', $LANG);
		$smarty->assign('cron_queue', Capsule::table('mod_solutedns_cron_queue')->count());
		$smarty->registerClass('Controller', 'WHMCS\Module\Addon\SoluteDNS\Admin\Controller');

		return $smarty->display($base_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin_index.tpl');
	}

	public function manage($vars) {
		// Get common module parameters
		$modulelink = $vars['modulelink'];
		$version = $vars['version'];
		$LANG = $vars['_lang'];
		$base_path = $vars['base_path'];

		// Domain ID
		$domain_id = (int) filter_input(INPUT_GET, "id", FILTER_VALIDATE_INT);

		// Set Smarty for SoluteDNS
		$smarty = new \Smarty();

		$smarty->caching = false;
		$smarty->compile_dir = $GLOBALS['templates_compiledir'];

		$smarty->assign('base_path', $base_path);
		$smarty->assign('LANG', $LANG);
		$smarty->assign('domain', $this->getDomain($domain_id));
		$smarty->registerClass('Controller', 'WHMCS\Module\Addon\SoluteDNS\Admin\Controller');

		// Get DNSsec keys
		$content = NULL;

		if (!empty($this->ns_details('dnssec_enable'))) {

			$time = time();

			$intl = Capsule::table('mod_solutedns_cache')->where('domain_id', $domain_id)->first();

			if (is_null($intl)) {

				$domain = $this->getDomain($domain_id);

				$zones = new Zones();

				$exists = $zones->exists($domain->domain);

				if ($exists == true) {

					$dnssec = new Dnssec();

					$result = $dnssec->get($domain->domain);

					if ($result['keys'] == NULL && $result['ds'] == NULL && $result['nsec'] == NULL) {

						$content = '';

						Capsule::table('mod_solutedns_cache')->insert([
							'domain_id' => $domain_id,
							'type' => 'dnssec',
							'content' => $content,
							'time' => $time,
						]);
					} else {
						$content = serialize($result);

						Capsule::table('mod_solutedns_cache')->insert([
							'domain_id' => $domain_id,
							'type' => 'dnssec',
							'content' => $content,
							'time' => $time,
						]);

						$content = $result;
					}
				}
			} else {
				$content = unserialize($intl->content);
			}

			$smarty->assign('dnssec', $content);
		}

		return $smarty->display($base_path . DIRECTORY_SEPARATOR . 'templates' . DIRECTORY_SEPARATOR . 'admin_manage.tpl');
	}

	/**
	 * Post action.
	 *
	 * @return json
	 */
	public function post($vars) {

		// Get common module parameters		
		$LANG = $vars['_lang']; // an array of the currently loaded language variables
		
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
		 * Action: Save posted Settings
		 */
		if ($data['action'] == 'settings') {

			foreach ($data as $key) {

				// Records
				if ($key['name'] == 'sdns_type_a') {
					$records[] = 'A,';
					continue;
				}
				if ($key['name'] == 'sdns_type_aaaa') {
					$records[] = 'AAAA,';
					continue;
				}
				if ($key['name'] == 'sdns_type_alias') {
					$records[] = 'ALIAS,';
					continue;
				}
				if ($key['name'] == 'sdns_type_caa') {
					$records[] = 'CAA,';
					continue;
				}
				if ($key['name'] == 'sdns_type_cname') {
					$records[] = 'CNAME,';
					continue;
				}
				if ($key['name'] == 'sdns_type_hinfo') {
					$records[] = 'HINFO,';
					continue;
				}
				if ($key['name'] == 'sdns_type_mx') {
					$records[] = 'MX,';
					continue;
				}
				if ($key['name'] == 'sdns_type_naptr') {
					$records[] = 'NAPTR,';
					continue;
				}
				if ($key['name'] == 'sdns_type_ns') {
					$records[] = 'NS,';
					continue;
				}
				if ($key['name'] == 'sdns_type_ptr') {
					$records[] = 'PTR,';
					continue;
				}
				if ($key['name'] == 'sdns_type_rp') {
					$records[] = 'RP,';
					continue;
				}
				if ($key['name'] == 'sdns_type_spf') {
					$records[] = 'SPF,';
					continue;
				}
				if ($key['name'] == 'sdns_type_srv') {
					$records[] = 'SRV,';
					continue;
				}
				if ($key['name'] == 'sdns_type_sshfp') {
					$records[] = 'SSHFP,';
					continue;
				}
				if ($key['name'] == 'sdns_type_tlsa') {
					$records[] = 'TLSA,';
					continue;
				}
				if ($key['name'] == 'sdns_type_txt') {
					$records[] = 'TXT,';
					continue;
				}

				// Delete status
				if ($key['name'] == 'sdns_deletestate_cancelled') {
					$delete[] = 'cancelled,';
					continue;
				}
				if ($key['name'] == 'sdns_deletestate_expired') {
					$delete[] = 'expired,';
					continue;
				}
				if ($key['name'] == 'sdns_deletestate_fraud') {
					$delete[] = 'fraud,';
					continue;
				}
				if ($key['name'] == 'sdns_deletestate_transferredaway') {
					$delete[] = 'transferredaway,';
					continue;
				}

				// Other fields
				if ($key['name'] == 'sdns_soa_hostmaster' && Validate::input($key['value'], 'default_ext') == true) {
					$values[0] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_hostmaster') {
					$error_fields[] = 'sdns_soa_hostmaster';
					continue;
				}

				if ($key['name'] == 'sdns_soa_serial' && Validate::input($key['value'], 'serial') == true) {
					$values[1] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_serial') {
					$error_fields[] = 'sdns_soa_serial';
					continue;
				}

				if ($key['name'] == 'sdns_soa_refresh' && Validate::input($key['value'], 'intl') == true) {
					$values[2] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_refresh') {
					$error_fields[] = 'sdns_soa_refresh';
					continue;
				}

				if ($key['name'] == 'sdns_soa_retry' && Validate::input($key['value'], 'intl') == true) {
					$values[3] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_retry') {
					$error_fields[] = 'sdns_soa_retry';
					continue;
				}

				if ($key['name'] == 'sdns_soa_expire' && Validate::input($key['value'], 'intl') == true) {
					$values[4] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_expire') {
					$error_fields[] = 'sdns_soa_expire';
					continue;
				}

				if ($key['name'] == 'sdns_soa_ttl' && Validate::input($key['value'], 'intl') == true) {
					$values[5] = $key['value'];
				} elseif ($key['name'] == 'sdns_soa_ttl') {
					$error_fields[] = 'sdns_soa_ttl';
					continue;
				}

				if ($key['name'] == 'sdns_soa_custom_primary' && Validate::input($key['value'], 'check') == true) {
					$values[6] = $key['value'];
				}

				if ($key['name'] == 'sdns_record_limit' && Validate::input($key['value'], 'int') == true) {
					$values[7] = $key['value'];
				} elseif ($key['name'] == 'sdns_record_limit') {
					$error_fields[] = 'sdns_record_limit';
					continue;
				}

				if ($key['name'] == 'sdns_respect_registrar' && Validate::input($key['value'], 'check') == true) {
					$values[8] = $key['value'];
				}

				if ($key['name'] == 'sdns_hide_soa' && Validate::input($key['value'], 'check') == true) {
					$values[9] = $key['value'];
				}

				if ($key['name'] == 'sdns_disable_ns' && Validate::input($key['value'], 'check') == true) {
					$values[10] = $key['value'];
				}

				if ($key['name'] == 'sdns_preset_ttl' && Validate::input($key['value'], 'check') == true) {
					$values[11] = $key['value'];
				}

				if ($key['name'] == 'sdns_dns_pagination' && Validate::input($key['value'], 'check') == true) {
					$values[12] = $key['value'];
				}

				if ($key['name'] == 'sdns_client_urlrewrite' && Validate::input($key['value'], 'default') == true) {
					$values[13] = $key['value'];
				} elseif ($key['name'] == 'sdns_client_urlrewrite') {
					$error_fields[] = 'sdns_client_urlrewrite';
					continue;
				}

				if ($key['name'] == 'sdns_auto_create' && Validate::input($key['value'], 'check') == true) {
					$values[14] = $key['value'];
				}

				if ($key['name'] == 'sdns_auto_delete_whmcs' && Validate::input($key['value'], 'check') == true) {
					$values[15] = $key['value'];
				}

				if ($key['name'] == 'sdns_auto_enable' && Validate::input($key['value'], 'check') == true) {
					$values[16] = $key['value'];
				}

				if ($key['name'] == 'sdns_auto_todo' && Validate::input($key['value'], 'check') == true) {
					$values[17] = $key['value'];
				}
			}

			// Save Allowed Records
			$records = isset($records) ? substr_replace(implode($records), "", -1) : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'record_types')->update(['value' => $records]);

			// Save Delete Status
			$delete = isset($delete) ? substr_replace(implode($delete), "", -1) : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'auto_delete')->update(['value' => $delete]);

			// Save Fields
			if (isset($values[0])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_hostmaster')->update(['value' => $values[0]]);
			}

			if (isset($values[1])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_serial')->update(['value' => $value[1]]);
			}

			if (isset($values[2])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_refresh')->update(['value' => $values[2]]);
			}

			if (isset($values[3])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_retry')->update(['value' => $values[3]]);
			}

			if (isset($values[4])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_expire')->update(['value' => $values[4]]);
			}

			if (isset($values[5])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'soa_ttl')->update(['value' => $values[5]]);
			}

			$value = isset($values[6]) ? $values[6] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'custom_primary')->update(['value' => $value]);

			if (isset($values[7])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'record_limit')->update(['value' => $values[7]]);
			}

			$value = isset($values[8]) ? $values[8] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'respect_registrar')->update(['value' => $value]);

			$value = isset($values[9]) ? $values[9] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'hide_soa')->update(['value' => $value]);

			$value = isset($values[10]) ? $values[10] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'disable_ns')->update(['value' => $value]);

			$value = isset($values[11]) ? $values[11] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'preset_ttl')->update(['value' => $value]);

			$value = isset($values[12]) ? $values[12] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'dns_pagination')->update(['value' => $value]);

			if (isset($values[13])) {
				Capsule::table('mod_solutedns_settings')->where('setting', 'client_urlrewrite')->update(['value' => $values[13]]);
			}

			$value = isset($values[14]) ? $values[14] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'auto_create')->update(['value' => $value]);

			$value = isset($values[15]) ? $values[15] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'auto_delete_whmcs')->update(['value' => $value]);

			$value = isset($values[16]) ? $values[16] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'auto_enabled')->update(['value' => $value]);

			$value = isset($values[17]) ? $values[17] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'auto_todo')->update(['value' => $value]);

			// Return status message
			if (isset($error_fields)) {
				$arr[] = [
					'status' => 'warning',
					'title' => $LANG['global_msg_changes_saved_title'],
					'msg' => $LANG['global_msg_changes_saved_exception'],
					'errorFields' => implode(",", $error_fields),
				];
			} else {
				$arr[] = [
					'status' => 'success',
					'title' => $LANG['global_msg_changes_saved_title'],
					'msg' => $LANG['global_msg_changes_saved_desc'],
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Save posted Nameserver details
		 */
		if ($data['action'] == 'nameserver') {

			foreach ($data as $key) {

				// Posted Fields
				if ($key['name'] == 'sdns_db_host' && Validate::input($key['value'], 'default') == true) {
					$value[0] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_db_host') {
					$error_fields[] = 'sdns_db_host';
					continue;
				}

				if ($key['name'] == 'sdns_db_port' && Validate::input($key['value'], 'intl') == true) {
					$value[1] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_db_port') {
					$error_fields[] = 'sdns_db_port';
					continue;
				}

				if ($key['name'] == 'sdns_db_user' && Validate::input($key['value'], 'default') == true) {
					$value[2] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_db_user') {
					$error_fields[] = 'sdns_db_user';
					continue;
				}

				if ($key['name'] == 'sdns_db_password' && Validate::input($key['value'], 'hash') == true) {
					$value[3] = $this->encrypt(isset($key['value']) ? $key['value'] : NULL);
				} elseif ($key['name'] == 'sdns_db_password') {
					$error_fields[] = 'sdns_db_password';
					continue;
				}

				if ($key['name'] == 'sdns_db_database' && Validate::input($key['value'], 'default') == true) {
					$value[4] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_db_database') {
					$error_fields[] = 'sdns_db_database';
					continue;
				}

				if ($key['name'] == 'sdns_zone_type' && Validate::input($key['value'], 'zonetype') == true) {
					$value[5] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_zone_type') {
					$error_fields[] = 'sdns_zone_type';
					continue;
				}


				if ($key['name'] == 'sdns_ns0' && Validate::input($key['value'], 'domain') == true) {
					$value[6] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns0') {
					$error_fields[] = 'sdns_ns0';
					continue;
				}

				if ($key['name'] == 'sdns_ns1' && Validate::input($key['value'], 'domain') == true) {
					$value[7] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns1') {
					$error_fields[] = 'sdns_ns1';
					continue;
				}

				if ($key['name'] == 'sdns_ns2' && Validate::input($key['value'], 'domain') == true) {
					$value[8] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns2') {
					$error_fields[] = 'sdns_ns2';
					continue;
				}

				if ($key['name'] == 'sdns_ns3' && Validate::input($key['value'], 'domain') == true) {
					$value[9] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns3') {
					$error_fields[] = 'sdns_ns3';
					continue;
				}

				if ($key['name'] == 'sdns_ns4' && Validate::input($key['value'], 'domain') == true) {
					$value[10] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns4') {
					$error_fields[] = 'sdns_ns4';
					continue;
				}

				if ($key['name'] == 'sdns_ns5' && Validate::input($key['value'], 'domain') == true) {
					$value[11] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ns5') {
					$error_fields[] = 'sdns_ns5';
					continue;
				}


				if ($key['name'] == 'sdns_ssh_host' && Validate::input($key['value'], 'default') == true) {
					$value[12] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ssh_host') {
					$error_fields[] = 'sdns_ssh_host';
					continue;
				}

				if ($key['name'] == 'sdns_ssh_port' && Validate::input($key['value'], 'intl') == true) {
					$value[13] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ssh_port') {
					$error_fields[] = 'sdns_ssh_port';
					continue;
				}

				if ($key['name'] == 'sdns_ssh_user' && Validate::input($key['value'], 'default') == true) {
					$value[14] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ssh_user') {
					$error_fields[] = 'sdns_ssh_user';
					continue;
				}

				if ($key['name'] == 'sdns_ssh_password' && Validate::input($key['value'], 'hash') == true) {
					$value[15] = $this->encrypt(isset($key['value']) ? $key['value'] : NULL);
				} elseif ($key['name'] == 'sdns_ssh_password') {
					$error_fields[] = 'sdns_ssh_password';
					continue;
				}

				if ($key['name'] == 'sdns_ssh_key' && Validate::input($key['value'], 'hash') == true) {
					$value[17] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_ssh_key') {
					$error_fields[] = 'sdns_ssh_key';
					continue;
				}


				if ($key['name'] == 'sdns_pdnsversion' && Validate::input($key['value'], 'intl') == true) {
					$value[23] = isset($key['value']) ? $key['value'] : NULL;
				} elseif ($key['name'] == 'sdns_pdnsversion') {
					$error_fields[] = 'sdns_pdnsversion';
					continue;
				}

				if ($key['name'] == 'sdns_dnssec_enable' && Validate::input($key['value'], 'check') == true) {
					$value[18] = isset($key['value']) ? $key['value'] : NULL;
				}

				if ($key['name'] == 'sdns_dnssec_rectify' && Validate::input($key['value'], 'check') == true) {
					$value[19] = isset($key['value']) ? $key['value'] : NULL;
				}

				if ($key['name'] == 'sdns_dnssec_auto' && Validate::input($key['value'], 'check') == true) {
					$value[20] = isset($key['value']) ? $key['value'] : NULL;
				}

				if ($key['name'] == 'sdns_dnssec_nsec3' && Validate::input($key['value'], 'check') == true) {
					$value[21] = isset($key['value']) ? $key['value'] : NULL;
				}

				if ($key['name'] == 'sdns_dnssec_client' && Validate::input($key['value'], 'check') == true) {
					$value[22] = isset($key['value']) ? $key['value'] : NULL;
				}
			}

			// Save Fields
			if (!isset($error_fields) && isset($value)) {
				Capsule::table('mod_solutedns_nameservers')->where('id', 1)->update([
					'db_host' => $value[0],
					'db_port' => $value[1],
					'db_user' => $value[2],
					'db_pass' => $value[3],
					'db_name' => $value[4],
					'zone_type' => $value[5],
					'ns0' => $value[6],
					'ns1' => $value[7],
					'ns2' => $value[8],
					'ns3' => $value[9],
					'ns4' => $value[10],
					'ns5' => $value[11],
					'ssh_host' => $value[12],
					'ssh_port' => $value[13],
					'ssh_user' => $value[14],
					'ssh_pass' => $value[15],
					'ssh_key' => $value[17],
					'dnssec_enable' => $value[18],
					'dnssec_rectify' => $value[19],
					'dnssec_auto' => $value[20],
					'dnssec_nsec3' => $value[21],
					'dnssec_client' => $value[22],
					'version' => $value[23],
				]);
			}

			// Return status message				
			if (isset($error_fields)) {
				$arr[] = [
					'status' => 'error',
					'title' => $LANG['global_msg_changes_unable_title'],
					'msg' => $LANG['global_msg_changes_unable_desc'],
					'errorFields' => implode(",", $error_fields),
				];
			} else {
				$arr[] = [
					'status' => 'success',
					'title' => $LANG['global_msg_changes_saved_title'],
					'msg' => $LANG['global_msg_changes_saved_desc'],
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Save posted Settings
		 */
		if ($data['action'] == 'system') {

			foreach ($data as $key) {

				if ($key['name'] == 'sdns_maintenance_mode' && Validate::input($key['value'], 'check') == true) {
					$values[0] = $key['value'];
				}

				if ($key['name'] == 'sdns_system_logging' && Validate::input($key['value'], 'check') == true) {
					$values[1] = $key['value'];
				}

				if ($key['name'] == 'sdns_system_license' && Validate::input($key['value'], 'license') == true) {
					$values[2] = $key['value'];
				}
			}

			// Save Fields
			$value = isset($values[0]) ? $values[0] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'maintenance')->update(['value' => $value]);

			$value = isset($values[1]) ? $values[1] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'logging')->update(['value' => $value]);

			$value = isset($values[2]) ? $values[2] : NULL;
			Capsule::table('mod_solutedns_settings')->where('setting', 'license')->update(['value' => $value]);
			
			// Purge license
			Core::purge();

			// Return status message
			$arr[] = [
				'status' => 'success',
				'title' => $LANG['global_msg_changes_saved_title'],
				'msg' => $LANG['global_msg_changes_saved_desc'],
				'pagereload' => true
			];

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Get System State
		 */
		if ($data['action'] == 'systemState') {

			$arr = NULL;

			if ($this->config('maintenance')) {

				$arr[] = [
					'status' => 'default',
					'title' => $LANG['admin_msg_maintenance_title'],
					'msg' => $LANG['admin_msg_maintenance_desc'],
					'fixed' => true,
				];
			}

			$core_data = $this->core();

			if ($core_data != NULL) {

				// System Check		
				$license_data = $core_data['license'];

				if ($license_data['status'] == 'database') {

					// Nameserver unavailable
					$arr[] = [
						'status' => 'error',
						'title' => $LANG['admin_msg_ns_unavailable_title'],
						'msg' => $LANG['admin_msg_ns_unavailable_desc'],
						'fixed' => true,
					];
				} elseif ($license_data["nextduedate"] != '0000-00-00') {

					// License expire warning
					$datetime1 = new \DateTime('now');
					$datetime2 = new \DateTime($license_data["nextduedate"]);

					$diff = $datetime1->diff($datetime2)->days;
					$diff = $diff + 1;

					if ($diff <= 15) {
						$arr[] = [
							'status' => 'warning',
							'title' => $LANG['admin_msg_license_expire_title'],
							'msg' => $LANG['admin_msg_license_expire_desc'],
							'fixed' => false,
						];
					}
				}

				// Zone check
				if ($license_data['status'] != 'database' && isset($data['zone'])) {

					$zones = new Zones();

					$exists = $zones->exists($data['zone']);

					if ($exists == false) {
						$arr[] = [
							'status' => 'warning',
							'title' => $LANG['global_msg_dns_nozone_title'],
							'msg' => $LANG['global_msg_dns_nozone_desc'] . ' ' . $LANG['global_msg_dns_nozonetemplate_desc'],
							'fixed' => true,
						];
					}

					$slave = $zones->slave($data['zone']);

					if ($slave == true) {
						$arr[] = [
							'status' => 'warning',
							'title' => $LANG['global_msg_dns_slave_title'],
							'msg' => $LANG['global_msg_dns_slave_desc'],
							'fixed' => true,
						];
					}
				}
			} else {

				$arr[] = [
					'status' => 'error',
					'title' => $LANG['admin_msg_core_undetected_title'],
					'msg' => $LANG['admin_msg_core_undetected_desc'],
					'fixed' => true,
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Get System State
		 */
		if ($data['action'] == 'systemCheck') {

			$arr = NULL;

			$core_data = $this->core();

			if ($core_data != NULL) {

				if ($data['type'] == 'db') {

					try {

						$db = db::get();

						$stmt = $db->prepare("SELECT id FROM domains LIMIT 1;");
						$stmt->execute();
						$stmt->fetch(\PDO::FETCH_ASSOC);

						$arr[] = [
							'status' => 'success',
							'title' => $LANG['admin_msg_core_db_successful_title'],
							'msg' => $LANG['admin_msg_core_db_successful_desc']
						];
					} catch (\Exception $ex) {

						$arr[] = [
							'status' => 'error',
							'title' => $LANG['admin_msg_core_db_error_title'],
							'msg' => $ex->getMessage(),
							'fixed' => true
						];
					}
				}
				if ($data['type'] == 'ssh') {

					try {

						$dnssec = new Dnssec();
						$result = $dnssec->session();

						if (is_array($result) && isset($result['error'])) {
							$arr[] = [
								'status' => 'error',
								'title' => $LANG['admin_msg_core_ssh_error_title'],
								'msg' => $result['error']['desc'],
								'fixed' => true
							];
						} else {
							$arr[] = [
								'status' => 'success',
								'title' => $LANG['admin_msg_core_ssh_successful_title'],
								'msg' => $LANG['admin_msg_core_ssh_successful_desc']
							];
						}
					} catch (\Exception $ex) {

						$arr[] = [
							'status' => 'error',
							'title' => $LANG['admin_msg_core_ssh_error_title'],
							'msg' => $ex->getMessage(),
							'fixed' => true
						];
					}
				}
			} else {

				$arr[] = [
					'status' => 'error',
					'title' => $LANG['admin_msg_core_undetected_title'],
					'msg' => $LANG['admin_msg_core_undetected_desc'],
					'fixed' => true,
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Add Template
		 */
		if ($data['action'] == 'addtemplate') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$data['name'] = empty($data['name']) ? '{domain}' : $data['name'];

				$send = [
					'domain' => 'temp.replacement',
					'name' => str_replace('{domain}', 'temp.replacement', $data['name']),
					'type' => $data['type'],
					'content' => str_replace("{domain}", 'temp.replacement', $data['content']),
					'ttl' => $data['ttl'],
					'prio' => $data['prio']
				];

				$result = Validate::template($send);

				if ($result == NULL) {

					$id = (int) $data['zone'];

					Capsule::table('mod_solutedns_template_records')->insert(
							[
								'product_id' => $id,
								'name' => $data['name'],
								'type' => $data['type'],
								'content' => $data['content'],
								'ttl' => $data['ttl'],
								'prio' => $data['prio']
							]
					);

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_added_title'],
						'msg' => $LANG['global_msg_dns_record_added_desc'],
						'fieldreset' => true,
						'tablereload' => true
					];
				} else {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_record_error_title'],
						'msg' => $this->getErrorMsg($result),
						'field' => $result['field']
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Edit Template
		 */
		if ($data['action'] == 'edittemplate') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$id = (int) $data['record_id'];
				$send = [
					'domain' => 'temp.replacement',
					'name' => str_replace('{domain}', 'temp.replacement', $data['name']),
					'type' => $data['type'],
					'content' => str_replace("{domain}", 'temp.replacement', $data['content']),
					'ttl' => $data['ttl'],
					'prio' => $data['prio']
				];

				$result = Validate::template($send);

				if ($result == NULL) {

					Capsule::table('mod_solutedns_template_records')->where('id', $id)->update(
							[
								'name' => $data['name'],
								'type' => $data['type'],
								'content' => $data['content'],
								'ttl' => $data['ttl'],
								'prio' => $data['prio']
							]
					);

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_edited_title'],
						'msg' => $LANG['global_msg_dns_record_edited_desc'],
						'fieldreset' => true,
						'tablereload' => true
					];
				} else {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_record_error_title'],
						'msg' => $this->getErrorMsg($result),
						'field' => $result['field'],
						'id' => $id,
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Delete Template
		 */
		if ($data['action'] == 'deletetemplate') {

			$id = (int) $data['record_id'];

			try {
				Capsule::table('mod_solutedns_template_records')->where('id', $id)->delete();

				$arr[] = [
					'status' => 'success',
					'title' => $LANG['global_msg_dns_record_deleted_title'],
					'msg' => $LANG['global_msg_dns_record_deleted_desc'],
					'tablereload' => true
				];
			} catch (\Exception $ex) {

				$arr[] = [
					'status' => 'error',
					'title' => $LANG['global_msg_dns_record_error_title'],
					'msg' => $LANG['global_msg_dns_error_occurred'],
					'id' => $id,
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Add Record
		 */
		if ($data['action'] == 'addrecord') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $data['zone'];
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
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_added_title'],
						'msg' => $LANG['global_msg_dns_record_added_desc'],
						'fieldreset' => true,
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = ['status' => 'error',
							'title' => $LANG['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
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

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $data['zone'];
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
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_edited_title'],
						'msg' => $LANG['global_msg_dns_record_edited_desc'],
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'error',
							'title' => $LANG['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
							'id' => $error['record']['id'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
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

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $data['zone'];
				$send['records'][] = [
					'id' => $data['record_id']
				];

				$records = new Records();
				$result = $records->delete($send);

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_deleted_title'],
						'msg' => $LANG['global_msg_dns_record_deleted_desc'],
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'error',
							'title' => $LANG['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
							'field' => $error['field'],
							'id' => $error['record']['id'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
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

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send['domain'] = $data['zone'];
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
							$this->add_crontask($data['zone'], 'rectify');
						}

						$arr[] = [
							'status' => 'success',
							'title' => sprintf($LANG['global_msg_dns_record_select_deleted_title'], $i),
							'msg' => $LANG['global_msg_dns_record_select_deleted_desc'],
							'tablereload' => true
						];
					}

					if (isset($result['errors'])) {

						foreach ($result['errors'] as $error) {

							$arr[] = [
								'status' => 'error',
								'title' => $LANG['global_msg_dns_record_error_title'],
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
							'title' => $LANG['global_msg_dns_error_occurred'],
							'msg' => $this->getErrorMsg($result['error']),
							'tablereload' => true
						];
					}
				} else {
					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_record_select_no_deleted_title'],
						'msg' => $LANG['global_msg_dns_record_select_no_deleted_desc'],
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: Apply Template
		 */
		if ($data['action'] == 'applytemplate') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				// Get template
				$template_id = (int) $data['template'];

				$records = Capsule::table('mod_solutedns_template_records')->select('name', 'type', 'content', 'ttl', 'prio')->where('product_id', $template_id)->get();

				// Create send array
				$send['domain'] = $data['zone'];

				foreach ($records as $record) {

					$send['records'][] = [
						'name' => str_replace(['{domain}'], $data['zone'], $record->name),
						'type' => $record->type,
						'content' => str_replace(['{domain}'], $data['zone'], $record->content),
						'ttl' => $record->ttl,
						'prio' => $record->prio,
					];
				}

				// Determine if zone exists
				$zones = new Zones();

				$exists = $zones->exists($send['domain']);

				if ($exists == true) {

					$records = new Records();
					$result = $records->add($send);
					$pagereload = false;
					$tablereload = true;
				} else {

					$result = $zones->add($send);
					$pagereload = true;
					$tablereload = false;
				}

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_template_apply_title'],
						'msg' => $LANG['global_msg_dns_template_apply_desc'],
						'pagereload' => $pagereload,
						'tablereload' => $tablereload
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$arr[] = [
							'status' => 'warning',
							'title' => $LANG['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error),
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
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
		 * Action: Import Zone
		 */
		if ($data['action'] == 'import') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send = [
					'domain' => $data['zone'],
					'zone' => $data['bind'],
					'overwrite' => $data['overwrite']
				];

				$zones = new Zones();
				$result = $zones->import($send);

				if (isset($result['success'])) {

					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_record_added_title'],
						'msg' => $LANG['global_msg_dns_record_added_desc'],
						'fieldreset' => true,
						'tablereload' => true
					];
				}

				if (isset($result['errors'])) {

					foreach ($result['errors'] as $error) {

						$error_record = '<br /><code>' . $error["record"]["type"] . ' | ' . $error["record"]["content"] . ' | ' . $error["record"]["name"] . ' | ' . $error["record"]["prio"] . ' | ' . $error["record"]["ttl"] . '</code>';
						$error_record = isset($error_record) ? $error_record : NULL;

						$arr[] = [
							'status' => 'error',
							'title' => $LANG['global_msg_dns_record_error_title'],
							'msg' => $this->getErrorMsg($error) . $error_record,
							'field' => $error['field'],
						];
					}
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
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
		 * Action: Export Zone
		 */
		if ($data['action'] == 'export') {

			$core_data = $this->core();

			if ($core_data != NULL) {

				$send = $data['zone'];

				$zones = new Zones();
				$result = $zones->export($send);

				if (isset($result['error'])) {

					http_response_code(500);
				} else {

					echo $result;
				}
			}

			exit();
		}

		/**
		 * Action: Delete Zone
		 */
		if ($data['action'] == 'deletezone') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$zones = new Zones();
				$result = $zones->delete($send);

				if (isset($result['success'])) {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();

					$arr[] = [
						'status' => 'success',
						'title' => ucfirst(sprintf($LANG['global_msg_dns_zone_deleted_title'], $send)),
						'msg' => sprintf($LANG['global_msg_dns_zone_deleted_desc'], $send),
						'pagereload' => true,
					];
				}

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Rectify
		 */
		if ($data['action'] == 'dnssec_rectify') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$dnssec = new Dnssec();
				$result = $dnssec->rectify($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error'])
					];
				} else {

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_rectified_title'],
						'msg' => $LANG['global_msg_dns_sec_rectified_desc']
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec NSEC
		 */
		if ($data['action'] == 'dnssec_nsec') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$dnssec = new Dnssec();
				$result = $dnssec->nsec($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error'])
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_nsec_title'],
						'msg' => $LANG['global_msg_dns_sec_nsec_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec NSEC3
		 */
		if ($data['action'] == 'dnssec_nsec3') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$dnssec = new Dnssec();
				$result = $dnssec->nsec3($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error'])
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_nsec3_title'],
						'msg' => $LANG['global_msg_dns_sec_nsec3_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Reload
		 */
		if ($data['action'] == 'dnssec_reload') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();

				$arr[] = [
					'status' => 'success',
					'title' => $LANG['global_msg_dns_sec_reload_title'],
					'msg' => $LANG['global_msg_dns_sec_reload_desc'],
					'pagereload' => true
				];
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec NSEC3
		 */
		if ($data['action'] == 'dnssec_reset') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$dnssec = new Dnssec();
				$result = $dnssec->unsecure($send);
				$result = $dnssec->secure($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error'])
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_reset_title'],
						'msg' => $LANG['global_msg_dns_sec_reset_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Unset
		 */
		if ($data['action'] == 'dnssec_unset') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = $domain->domain;

				$dnssec = new Dnssec();
				$result = $dnssec->unsecure($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_unset_title'],
						'msg' => $LANG['global_msg_dns_sec_unset_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Add Key
		 */
		if ($data['action'] == 'dnssec_addkey') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = [
					'domain' => $domain->domain,
					'flag' => $data['flag'],
					'bits' => $data['bits'],
					'algorithm' => $data['algorithm'],
				];

				$dnssec = new Dnssec();
				$result = $dnssec->add_key($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_addkey_title'],
						'msg' => $LANG['global_msg_dns_sec_addkey_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Activate Key
		 */
		if ($data['action'] == 'dnssec_activatekey') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = [
					'domain' => $domain->domain,
					'id' => $data['key'],
				];

				$dnssec = new Dnssec();
				$result = $dnssec->activate_key($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_activatekey_title'],
						'msg' => $LANG['global_msg_dns_sec_activatekey_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Deactivate Key
		 */
		if ($data['action'] == 'dnssec_deactivatekey') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = [
					'domain' => $domain->domain,
					'id' => $data['key'],
				];

				$dnssec = new Dnssec();
				$result = $dnssec->deactivate_key($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_deactivatekey_title'],
						'msg' => $LANG['global_msg_dns_sec_deactivatekey_desc'],
						'pagereload' => true
					];
				}
			}

			echo json_encode($arr);
			exit();
		}

		/**
		 * Action: DNSsec Delete Key
		 */
		if ($data['action'] == 'dnssec_deletekey') {

			// Check maintenance mode
			$this->getMaintenance();

			$core_data = $this->core();

			if ($core_data != NULL) {

				$domain = $this->getDomain((int) $data['zone']);

				$send = [
					'domain' => $domain->domain,
					'id' => $data['key'],
				];

				$dnssec = new Dnssec();
				$result = $dnssec->delete_key($send);

				if (isset($result['error'])) {

					$arr[] = [
						'status' => 'error',
						'title' => $LANG['global_msg_dns_error_occurred'],
						'msg' => $this->getErrorMsg($result['error']),
					];
				} else {

					Capsule::table('mod_solutedns_cache')->where('domain_id', (int) $data['zone'])->delete();
					if ($this->ns_details('dnssec_rectify')) {
						$this->add_crontask($data['zone'], 'rectify');
					}

					$arr[] = [
						'status' => 'success',
						'title' => $LANG['global_msg_dns_sec_deletekey_title'],
						'msg' => $LANG['global_msg_dns_sec_deletekey_desc'],
						'pagereload' => true
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
			'title' => $LANG['global_msg_invalid_request_title'],
			'msg' => $LANG['global_msg_invalid_request_desc'],
		];

		echo json_encode($arr);
		exit();
	}

	/**
	 * GET action.
	 */
	public function get($data) {

		// Set headers
		header('Pragma: no-cache');
		header('Cache-Control: no-store, no-cache, must-revalidate');

		// Set language variable
		$this->lang = $data['_lang'];

		if (isset($_GET['table'])) {

			$domain_id = isset($_GET['data']) ? (int) filter_input(INPUT_GET, "data", FILTER_SANITIZE_NUMBER_INT) : '0';

			// Set table template or records
			if ($_GET['table'] == 'sdns_template_records') {
				$type = 'local';
				$table = 'mod_solutedns_template_records';
				$primaryKey = 'id';
			} else {
				$type = 'remote';
				$table = 'records';
				$primaryKey = 'id';
			}

			// Get system state
			if ($table == 'records') {
				$this->maintenance = empty($this->config('maintenance')) ? NULL : 'DISABLED';
			} else {
				$this->maintenance = NULL;
			}

			// Get remote ID
			if ($type == 'remote') {
				$getDomain = $this->getDomain($domain_id);
				
				// IDN formatting
				$domain = $this->idn($getDomain->domain);

				try {

					$db = db::get();
					$stmt = $db->prepare("SELECT id FROM domains where name = :domain");
					$stmt->execute([':domain' => $domain]);
					$row = $stmt->fetch(\PDO::FETCH_ASSOC);

					$domain_id = $row['id'];
				} catch (\ErrorException $e) {
					SSP::fatal(
							"An error occurred while connecting to the database. " .
							"The error reported by the server was: " . $e->getMessage()
					);
				}
			}

			// Set database
			$ns_details = [
				'type' => $type,
			];

			// Columns
			$columns = [
				[
					'db' => 'id', 'dt' => 0,
					'formatter' => function( $d, $row ) {
						return '<div class="checkbox tablecheckbox"><input type="checkbox" name="sdns_select" id="sdns_select_' . $row['id'] . '" value="' . $row['id'] . '" style="display: hidden;" ' . $this->maintenance . '/><label for="sdns_select_' . $row['id'] . '" /></div>';
					}
				],
				[
					'db' => 'id', 'dt' => 1,
					'formatter' => function( $d, $row ) {
						return '<div class="text-center">' . $d . '</div>';
					}
				],
				[
					'db' => 'name', 'dt' => 2,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-name_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_name_' . $row['id'] . '" id="sdns_name_' . $row['id'] . '" value="' . $row['name'] . '"></div>';
					}
				],
				[
					'db' => 'type', 'dt' => 3,
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
					'db' => 'content', 'dt' => 4,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-content_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_content_' . $row['id'] . '" id="sdns_content_' . $row['id'] . '" value="' . htmlentities($row['content']) . '"></div>';
					}
				],
				[
					'db' => 'prio', 'dt' => 5,
					'formatter' => function( $d, $row ) {
						return '<div id="sdns_z-prio_' . $row['id'] . '"><input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_prio_' . $row['id'] . '" id="sdns_prio_' . $row['id'] . '" value="' . $row['prio'] . '"></div>';
					}
				],
				[
					'db' => 'ttl', 'dt' => 6,
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
							return '<input DISABLED type="textbox" class="form-padding form-control dnsfield" name="sdns_ttl_' . $row['id'] . '" id="sdns_ttl_' . $row['id'] . '" value="' . $row['ttl'] . '">';
						}
					}
				],
				[
					'db' => 'id', 'dt' => 7,
					'formatter' => function( $d, $row ) {
						if ($_GET['table'] == 'sdns_template_records') {
							return '<div class="text-center text-nowrap"><button type="button" class="btn btn-sm btn-success" style="display: none;" id="sdns_save_' . $row['id'] . '" onclick="record_edit(\'template\', ' . $row['id'] . ')"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-warning" id="sdns_edit_' . $row['id'] . '" onclick="edit(\'' . $row['id'] . '\')"><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#dialog_deleteRecord" onclick="setRecord(\'' . $row['id'] . '\')"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div>';
						} else {
							return '<div class="text-center text-nowrap"><button type="button" class="btn btn-sm btn-success" style="display: none;" id="sdns_save_' . $row['id'] . '" onclick="record_edit(\'zone\', ' . $row['id'] . ')" ' . $this->maintenance . '><span class="glyphicon glyphicon-ok" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-warning" id="sdns_edit_' . $row['id'] . '" onclick="edit(\'' . $row['id'] . '\')" ' . $this->maintenance . '><span class="glyphicon glyphicon-pencil" aria-hidden="true"></span></button> <button type="button" class="btn btn-sm btn-danger" data-toggle="modal" data-target="#dialog_deleteRecord" onclick="setRecord(\'' . $row['id'] . '\')" ' . $this->maintenance . '><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button></div>';
						}
					}
				]
			];

			// Where condition
			if ($_GET['table'] == 'sdns_template_records') {
				$where = "product_id = '$domain_id'";
			} else {
				$where = "domain_id = '$domain_id' AND NOT (type = '')";
			}

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

	public function ns_details($setting) {

		return Capsule::table('mod_solutedns_nameservers')->select($setting)->where('id', '1')->first()->$setting;
	}

	/**
	 * Get product list.
	 */
	public function product_list() {

		return Capsule::table('tblproducts')->select(['id', 'name'])->get();
	}

	/**
	 * Get version information.
	 */
	public static function version() {

		return Capsule::table('tbladdonmodules')->select('value')->where('module', 'solutedns')->where('setting', 'version')->first()->value;
	}

	/**
	 * Set/Detect the SoluteDNS Core.
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
			return mb_strtolower(idn_to_ascii($domain));
		} else {
			return mb_strtolower(utf8_encode($domain));
		}
	}
	
	/**
	 * IDN check
	 */
	public static function idnCheck() {
		
		if (extension_loaded('intl')) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Get domain
	 */
	public function getDomain($domain_id) {

		if (isset($domain_id)) {
			return Capsule::table('tbldomains')->select('id', 'userid', 'domain')->where('id', $domain_id)->first();
		} else {
			return false;
		}
	}

	/**
	 * Get maintenance status.
	 */
	public function getMaintenance() {

		global $_ADDONLANG;

		$this->maintenance = empty($this->config('maintenance')) ? false : true;

		if ($this->maintenance) {

			$arr[] = [
				'status' => 'error',
				'title' => $_ADDONLANG['admin_msg_maintenance_title'],
				'msg' => $_ADDONLANG['client_msg_system_error_desc'],
			];

			echo json_encode($arr);
			exit();
		}
	}

	/**
	 * Convert error message.
	 */
	public function getErrorMsg($error) {

		global $_ADDONLANG;

		if (is_numeric($error['code'])) {
			if ($error['code'] == '6004') {

				return $error['desc'];
			} else {

				$code = 'global_error_' . $error['code'];
				return $_ADDONLANG[$code];
			}
		} else {

			$code = 'global_validation_' . $error['code'];
			$desc = $_ADDONLANG[$code];
			$part = isset($error['part']) ? $error['part'] : NULL;

			return sprintf($desc, $part);
		}
	}

	/**
	 * Encrypt Information.
	 */
	public function encrypt($data, $keysize = NULL) {

		try {

			// Get Secret Key
			global $cc_encryption_hash;
			$key = $cc_encryption_hash;

			// Set Crypt
			$cipher = new AES(); // WHMCS 7+
			// Keys can range in length from 32 bits to 448 in steps of 8
			$cipher->setKey($key);

			if ($keysize != NULL) {
				(int) $keysize;
				$size = 2 * $keysize;
			} else {
				$size = 2 * 512;
			}

			$plaintext = str_repeat($data . ';', $size);

			return base64_encode($cipher->encrypt($plaintext));
		} catch (Exception $e) {
			return false;
		}
	}

	/**
	 * Decrypt information.
	 */
	public function decrypt($data) {

		try {
			// Get Secret Key
			global $cc_encryption_hash;
			$key = $cc_encryption_hash;

			// Set Crypt
			$cipher = new AES(); // WHMCS 7+
			// Keys can range in length from 32 bits to 448 in steps of 8
			$cipher->setKey($key);

			$data = base64_decode($data);
			$result = $cipher->decrypt($data);

			$raw_decoded = implode(',', array_unique(explode(';', $result)));
			$decoded = substr_replace($raw_decoded, "", -1);

			return $decoded;
		} catch (Exception $e) {
			return false;
		}
	}

}
