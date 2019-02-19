<?php

/**
 *               *** SoluteDNS Community Edition for WHMCS ***
 *
 * @file        hooks.php
 * @package     solutedns-ce-whmcs
 *
 * Copyright (c) 2018 NetDistrict
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

/**
 * Required Classes.
 */
use WHMCS\Database\Capsule;
use WHMCS\View\Menu\Item as MenuItem;
use WHMCS\ClientArea;
use WHMCS\Module\Addon\SoluteDNS\Admin\Controller as SDNS_Controller;
use WHMCS\Module\Addon\SoluteDNS\System\Cron;
use solutedns\Dns\Zones;

$loader = new \Composer\Autoload\ClassLoader();
$loader->addPsr4('solutedns\\', __DIR__ . DIRECTORY_SEPARATOR . 'lib' . DIRECTORY_SEPARATOR . 'Core');
$loader->register();

/**
 * Automatically create zones
 *
 * Add's an field to the admin's domain details field for DNS Management.
 */
add_hook('AfterShoppingCartCheckout', 1, function($vars) {

	try {

		// Set Classes
		$zones = new Zones();

		if (SDNS_Controller::Config('auto_create')) {

			// Set Params
			$domains = $vars['DomainIDs'];
			$services = $vars['ServiceIDs'];
			$entry = NULL;

			foreach ($domains as $domain_id) {

				$data = Capsule::table('tbldomains')->select('domain', 'dnsmanagement')->where('id', $domain_id)->first();

				if ($data->dnsmanagement || SDNS_Controller::Config('auto_enabled')) {

					// Make sure DNS Management is enabled
					Capsule::table('tbldomains')->where('id', $domain_id)->update(['dnsmanagement' => '1',]);

					// Add entry for processing
					$entry[$domain]['domain'] = mb_strtolower($data->domain);
				}
			}

			foreach ($services as $service_id) {

				$data = Capsule::table('tblhosting')->select('domain', 'packageid')->where('id', $service_id)->first();

				// Add entry for processing product records
				$domain = mb_strtolower($data->domain);
				$entry[$domain]['product'] = $data->packageid;
			}

			foreach ($entry as $create) {

				if (isset($create['domain'])) {

					// Get Records
					$product_id = isset($create['product']) ? $create['product'] : '0';
					$records = json_encode(Capsule::table('mod_solutedns_template_records')->select('name', 'type', 'content', 'ttl', 'prio')->where('product_id', $product_id)->get());

					// Create zone
					$send = [
						'domain' => $create['domain'],
						'records' => json_decode(str_replace('{domain}', $create['domain'], $records), true),
					];

					$result = $zones->add($send);

					// Process result
					if (isset($result['success'])) {

						if (SDNS_Controller::Config('logging')) {
							logActivity('A new zone was automatically created for: ' . $create['domain'] . '.', 0);
						}
					}

					if (isset($result['dnssec']['errors'])) {

						foreach ($result['dnssec']['errors'] as $error) {

							$todo[] = 'DNS ERROR [' . $error['code'] . ']: ' . $error['desc'];
							if (SDNS_Controller::Config('logging')) {
								logActivity('DNS ERROR [' . $error['code'] . '] for ' . $create['domain'] . ': ' . $error['desc'], 0);
							}
							unset($error);
						}
					}

					if (isset($result['error'])) {

						$error = $result['error'];

						$todo[] = 'DNS ERROR [' . $error['code'] . ']: ' . $error['desc'];
						if (SDNS_Controller::Config('logging')) {
							logActivity('DNS ERROR [' . $error['code'] . '] for ' . $create['domain'] . ': ' . $error['desc'], 0);
						}
						unset($error);
					}

					// Add To-Do item
					if (isset($todo) && SDNS_Controller::Config('auto_todo')) {

						Capsule::table('tbltodolist')->insert(
								[
									'date' => date("Y-m-d"),
									'title' => 'DNS action required for: ' . $create['domain'],
									'description' => implode("\n", $todo),
									'status' => 'New',
									'duedate' => date("Y-m-d"),
								]
						);

						unset($todo);
					}
				}
			}
		}
	} catch (Exception $e) {
		logActivity('DNS ERROR [AfterShoppingCartCheckout]:' . $e->getMessage(), 0);
	}
});


/**
 * Automatically delete zone
 *
 * Delete zone when the domain is deleted from WHMCS.
 */
add_hook('DomainDelete', 1, function($vars) {

	try {

		if (SDNS_Controller::Config('auto_delete_whmcs')) {

			// Check if no other registration exists
			$data = Capsule::table('tbldomains')->select('domain')->where('id', $vars['domainid'])->first();
			$domain = mb_strtolower($data->domain);
			$count = Capsule::table('tbldomains')->where('domain', $data->domain)->count();

			if ($count <= 1) {

				// Check if zone exists
				$zones = new Zones();
				$result = $zones->exists($domain);

				if ($result == true) {

					// Remove zone
					$result = $zones->delete($domain);

					// Process result
					if (isset($result['success'])) {

						if (SDNS_Controller::Config('logging')) {
							logActivity('A zone was automatically deleted for: ' . $domain . '.', 0);
						}
					}
					if (isset($result['error'])) {

						$error = $result['error'];

						$todo[] = 'DNS ERROR [' . $error['code'] . ']: ' . $error['desc'];
						if (SDNS_Controller::Config('logging')) {
							logActivity('DNS ERROR [' . $error['code'] . '] for ' . $domain . ': ' . $error['desc'], 0);
						}
					}
				}
			}
		}
	} catch (Exception $e) {
		logActivity('DNS ERROR [DomainDelete]:' . $e->getMessage(), 0);
	}
});


/**
 * Automatically delete zones on update
 *
 * Delete a zone when the domain status is edited to one of the configured statuses.
 */
add_hook('DomainEdit', 1, function($vars) {

	try {

		if (SDNS_Controller::Config('auto_delete')) {

			$status = SDNS_Controller::inConfig('auto_delete');

			// Check if no other registration exists
			$data = Capsule::table('tbldomains')->select('domain', 'status')->where('id', $vars['domainid'])->first();
			$domain = mb_strtolower($data->domain);
			$count = Capsule::table('tbldomains')
					->where([['domain', $data->domain], ['status', 'Active']])
					->orWhere([['domain', $data->domain], ['status', 'Pending']])
					->orWhere([['domain', $data->domain], ['status', 'Pending Transfer']])
					->count();

			if ($count <= 1) {

				// Check if status is configured
				if (in_array(strtolower($data->status), $status)) {

					// Check if zone exists
					$zones = new Zones();
					$result = $zones->exists($domain);

					if ($result == true) {

						// Remove zone
						$result = $zones->delete($domain);

						// Process result
						if (isset($result['success'])) {

							if (SDNS_Controller::Config('logging')) {
								logActivity('A zone was automatically deleted for: ' . $domain . '.', 0);
							}
						}
						if (isset($result['error'])) {

							$error = $result['error'];

							$todo[] = 'DNS ERROR [' . $error['code'] . ']: ' . $error['desc'];
							if (SDNS_Controller::Config('logging')) {
								logActivity('DNS ERROR [' . $error['code'] . '] for ' . $domain . ': ' . $error['desc'], 0);
							}
						}
					}
				}
			}
		}
	} catch (Exception $e) {
		logActivity('DNS ERROR [DomainEdit]:' . $e->getMessage(), 0);
	}
});


/**
 * Cron Tasks
 */
add_hook('AfterCronJob', 1, function($vars) {

	// Call cron class
	$cron = new Cron();
	$cron->run();
});


/**
 * Menu Management
 *
 * Add's menu and sidebar entries to the client area.
 */
add_hook('ClientAreaPrimarySidebar', 1, function(MenuItem $primarySidebar) {

	try {
		
		if ($_SESSION['uid']) {

			// Set Domain ID and DNS Management state
			$tbldata = NULL;
	
			if (isset($_REQUEST['id'])) {
	
				$domain_id = (int) filter_input(INPUT_GET, "id", FILTER_SANITIZE_NUMBER_INT);
	
				$tbldata = Capsule::table('tbldomains')->select('dnsmanagement')->where('id', $domain_id)->first();
			}
	
			// Get custom URL
			$custom_url = SDNS_Controller::config('client_urlrewrite');
			$custom_url = !empty($custom_url) ? $custom_url . '/' : 'index.php?m=solutedns&id=';
	
			// Add Sidebar to DNS Management page
			if (App::getCurrentFilename() == 'index' && isset($_REQUEST['m']) && $_REQUEST['m'] == 'solutedns') {
	
				// Primary Sidebar
				if (is_null($primarySidebar->getChild('Domain Details Management'))) {
	
					$primarySidebar->addChild('Domain Details Management')
							->setLabel(Lang::trans('manage'))
							->setIcon('fa-gear');
	
					$primarySidebar->getChild('Domain Details Management')
							->addChild('Overview')
							->setLabel(Lang::trans('overview'))
							->setUri('clientarea.php?action=domaindetails&id=' . $domain_id . '#tabOverview')
							->setOrder(10);
	
					$primarySidebar->getChild('Domain Details Management')
							->addChild('Auto Renew Settings')
							->setLabel(Lang::trans('domainsautorenew'))
							->setUri('clientarea.php?action=domaindetails&id=' . $domain_id . '#tabAutorenew')
							->setOrder(20);
	
					$primarySidebar->getChild('Domain Details Management')
							->addChild('Domain Addons')
							->setLabel(Lang::trans('domainaddons'))
							->setUri('clientarea.php?action=domaindetails&id=' . $domain_id . '#tabAddons')
							->setOrder(30);
	
					$primarySidebar->getChild('Domain Details Management')
							->addChild('Manage DNS Host Records')
							->setLabel(Lang::trans('domaindnsmanagement'))
							->setUri($custom_url . $domain_id)
							->setClass('active')
							->setOrder(40);
				}
			}
	
			// Set or Update DNS Management menu item
			if (App::getCurrentFilename() == 'clientarea' && isset($_REQUEST['action']) && $_REQUEST['action'] == 'domaindetails') {
	
				if (!is_null($primarySidebar->getChild('Domain Details Management')->getChild('Manage DNS Host Records')) && empty(SDNS_Controller::config('respect_registrar'))) {
	
					$primarySidebar->getChild('Domain Details Management')
							->getChild('Manage DNS Host Records')
							->setUri($custom_url . $domain_id);
				}
	
				if (is_null($primarySidebar->getChild('Domain Details Management')->getChild('Manage DNS Host Records')) && $tbldata->dnsmanagement == 1) {
	
					$primarySidebar->getChild('Domain Details Management')
							->addChild('Manage DNS Host Records')
							->setLabel(Lang::trans('domaindnsmanagement'))
							->setUri($custom_url . $domain_id)
							->setOrder(100);
				}
			}
		}
	} catch (Exception $e) {
		logActivity('DNS ERROR [ClientAreaPrimarySidebar]:' . $e->getMessage(), 0);
	}
});

/**
 * Redirect WHMCS DNS to SoluteDNS
 */
add_hook('ClientAreaPageDomainDNSManagement', 1, function($vars) {	
	if ($_SESSION['uid'] && App::getCurrentFilename() == 'clientarea' && filter_input(INPUT_GET, 'action', FILTER_SANITIZE_SPECIAL_CHARS) == 'domaindns' && !empty(SDNS_Controller::config('force_dns'))) {
		
		$domain_id = filter_input(INPUT_GET, 'domainid', FILTER_VALIDATE_INT);
		
		if ($domain_id) {
			
			// Get custom URL	
			$custom_url = !empty(SDNS_Controller::config('client_urlrewrite')) ? SDNS_Controller::config('client_urlrewrite') : NULL;
			
			// Get system URL
			$system_url = Capsule::table('tblconfiguration')->select('value')->where('setting', 'SystemURL')->first()->value;
			
			// Redirect to SoluteDNS
			$url = !is_null($custom_url) ? $system_url.$custom_url.'/'.$domain_id : 'index.php?m=solutedns&id='.$domain_id;
	
			header('Location: '.$url, true, '302');
			exit();
			
		}
		
	}
	
});

/**
 * Admin Tab Field
 *
 * Add's an field to the admin's domain details field for DNS Management.
 */
add_hook('AdminClientDomainsTabFields', 1, function($vars) {

	return [
		Lang::trans('domaindnsmanagement') => '<a class="btn btn-default btn-sm" href="addonmodules.php?module=solutedns&action=manage&id=' . $vars['id'] . '" class="button"><span class="glyphicon glyphicon-globe" aria-hidden="true"></span> Manage</a>',
	];
});

/**
 * Admin HTML header
 *
 * Add's required HTML resources to the head for SoluteDNS pages.
 */
add_hook('AdminAreaHeadOutput', 1, function($vars) {

	try {

		if (!isset($_GET['module']) || $_GET['module'] != 'solutedns') {
			return NULL;
		}

		$paging = SDNS_Controller::Config('dns_pagination') ? 'true' : 'false';

		return
				<<<HTML
	<!-- SoluteDNS CSS -->
	<link href="../modules/addons/solutedns/templates/css/admin.css" rel="stylesheet" type="text/css" />
	
	
	<!-- SoluteDNS Scripts -->
	<script type="text/javascript" src="../modules/addons/solutedns/templates/js/nprogress.min.js"></script>
	<script type="text/javascript" src="../modules/addons/solutedns/templates/js/admin.js"></script>
	<script type="text/javascript" src="../assets/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="../assets/js/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="../assets/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" src="../modules/addons/solutedns/templates/js/dataTables.fnReloadAjax.js"></script>

	<script>
		function drawRecords(nTable, nData) {
			
			if ( isDataTable ( nTable ) == false ) {
				SDNS_recordTable = $('#'+nTable).dataTable( {	
					"columns": [
						{ "width": "5%", "orderable": false },
						{ "width": "5%", "type": "num-html" },
						{ "width": "20%" },
						{ "width": "12%" },
						{ "width": "25%" },
						{ "width": "11%", "orderable": false },
						{ "width": "11%", "orderable": false },
						{ "width": "11%", "orderable": false }
					],
					"processing": true,
					"serverSide": true,
					"responsive": true,
					"ajax": location.protocol + '//' + location.host + location.pathname + '?module=solutedns&action=get&table=' + nTable + '&data=' + nData,
					"stateSave": true,
					"sorting": [[1,"asc"]],
					"paging": {$paging},
					"searching": false,
					"language": {
                            "processing": '<div class="data_spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div',
					},
					"fnStateSaveParams": function (oSettings, oData) {
					},
					"fnPreDrawCallback": function( oSettings ) {
						NProgress.start();
					},
					"fnDrawCallback": function( oSettings ) {
					  NProgress.done();
					}
				});
			}
		}
	</script>
HTML;
	} catch (Exception $e) {
		logActivity('DNS ERROR [AdminAreaHeadOutput]:' . $e->getMessage(), 0);
	}
});

/**
 * Client HTML header
 *
 * Add's required HTML resources to the head for SoluteDNS pages.
 */
add_hook('ClientAreaHeadOutput', 1, function($vars) {

	try {
	
		if ($_SESSION['uid']) {

			if (
					$vars['filename'] == 'index' && App::isInRequest('m') && App::getFromRequest('m') == 'solutedns'
			) {
	
				// Get custom URL
				if (substr($_SERVER[REQUEST_URI],-1) == '/') {
					$custom_url = !empty(SDNS_Controller::config('client_urlrewrite')) ? '../' : NULL;
				} else {
					$custom_url = NULL;
				}
				
	
				// Set paging
				$paging = SDNS_Controller::Config('dns_pagination') ? 'true' : 'false';
	
				return
					<<<HTML
<!-- SoluteDNS CSS -->
	<link href="{$custom_url}modules/addons/solutedns/templates/css/client.css" rel="stylesheet" type="text/css" />
	
	
	<!-- SoluteDNS Scripts -->
	<script type="text/javascript" src="{$custom_url}modules/addons/solutedns/templates/js/nprogress.min.js"></script>
	<script type="text/javascript" src="{$custom_url}modules/addons/solutedns/templates/js/client.js"></script>
	<script type="text/javascript" src="{$custom_url}assets/js/jquery.dataTables.min.js"></script>
	<script type="text/javascript" src="{$custom_url}assets/js/dataTables.bootstrap.min.js"></script>
	<script type="text/javascript" src="{$custom_url}assets/js/dataTables.responsive.min.js"></script>
	<script type="text/javascript" src="{$custom_url}modules/addons/solutedns/templates/js/dataTables.fnReloadAjax.js"></script>

	<script>
		function drawRecords(nTable, nData) {
			if ( isDataTable ( nTable ) == false ) {
				SDNS_recordTable = $('#'+nTable).dataTable( {
					"dom": '<"listtable"ft>prl', 	
					"columns": [
						{ "width": "5%", "orderable": false },
						{ "width": "23%" },
						{ "width": "12%" },
						{ "width": "27%" },
						{ "width": "8%", "orderable": false },
						{ "width": "14%", "orderable": false },
						{ "width": "11%", "orderable": false }
					],
					"processing": true,
					"serverSide": true,
					"responsive": true,
					"ajax": '{$custom_url}index.php?m=solutedns&action=get&table=' + nTable + '&data=' + nData,
					"stateSave": true,
					"sorting": [[1,"asc"]],
					"paging": {$paging},
					"searching": false,
					"language": {
                            "processing": '<div class="data_spinner"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div',
					},
					"fnStateSaveParams": function (oSettings, oData) {
					},
					"fnPreDrawCallback": function( oSettings ) {
						NProgress.start();
					},
					"fnDrawCallback": function( oSettings ) {
					  NProgress.done();
					}
				});

			}
		}
	</script>
HTML;
			} else {
				return NULL;
			}
		}
	} catch (Exception $e) {
		logActivity('DNS ERROR [ClientAreaHeadOutput]:' . $e->getMessage(), 0);
	}
});
