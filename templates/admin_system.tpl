{assign var=core value=Controller::core()} 

<h2>{$LANG.admin_menu_system}</h2>

<form role="form" id="system" class="label-form">
	<fieldset>
		<input type="hidden" name="sdns_form" value="system">

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_module_version}:</p>
			</div>
			<div class="col-md-9">
				<p>{Controller::version()}</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_core_version}:</p>
			</div>
			<div class="col-md-9">
				<p>{$core.version}</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_idn_support}:</p>
			</div>
			<div class="col-md-9">
				<p>{if Controller::idnCheck() eq true}<span class="label label-success">{$LANG.global_status_enabled}</span>{else}<span class="label label-default">{$LANG.global_status_disabled}</span>{/if}</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_cron_status}:</p>
			</div>
			<div class="col-md-9">
				<p>{$LANG.admin_system_cron_status_tasks} <span class="label label-primary">{$cron_queue}</span> | {$LANG.admin_system_cron_status_run} <i>{if Controller::config(last_cron)}{Controller::config(last_cron)|date_format:"%B %e, %Y (%T)"}{else}{$LANG.admin_never}{/if}</i>
			</div>
		</div>

		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_system_logging">{$LANG.admin_system_system_logging}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(logging)}checked {/if}name="sdns_system_logging" id="sdns_system_logging" type="checkbox">
					<label for="sdns_system_logging">{$LANG.admin_system_system_logging_desc}</label>
				</div>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_maintenance_mode">{$LANG.admin_system_maintenance_mode}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(maintenance)}checked {/if}name="sdns_maintenance_mode" id="sdns_maintenance_mode" type="checkbox">
					<label for="sdns_maintenance_mode">{$LANG.admin_system_maintenance_mode_desc}</label>
				</div>
			</div>
		</div>

		<hr />
		<h3>{$LANG.admin_system_license}</h3>

		<div class="row">
			<div class="col-md-3 text-right  title">
				<p>{$LANG.admin_system_license_product}:</p>
			</div>
			<div class="col-md-9">
				<p><strong>SoluteDNS for WHMCS</strong></p>
			</div>
		</div>

		<div class="row">

			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_license_edition}:<p>
			</div>
			<div class="col-md-9">
				<p>Community Edition</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_license_status}:</p>
			</div>
			<div class="col-md-9">
				<p><span class="label active">Active</span></p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3 text-right title">
				<p>{$LANG.admin_system_license_expires}:</p>
			</div>
			<div class="col-md-9">
				<p>{$LANG.admin_never}</p>
			</div>
		</div>

		<div class="row">
			<div class="col-md-3"></div>
			<div class="col-md-9">
				<hr />
			</div>
		</div>

		{if $core.license}
			{if $core.license.status eq 'Invalid'}
				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_product}:</p>
					</div>
					<div class="col-md-9">
						<p><strong>SoluteDNS Core</strong></p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">

						<div class="bootstrap">
							<div class="alert2 alert2-danger">
								<h4>License {$core.license.status}</h4>
								<p>Your license key is invalid which may be caused by several reasons including:<br /><br />- An incorrect license key has been entered.<br />- The domain to access this installation has changed.<br />- The IP of this installation has changed.<br />- The installation directory has changed.</p>
							</div>
						</div>
						<center><p class="text-danger">You can reissue your license key on-demand from our client area when required, or enter a different license key below. <br />If you are not sure what caused this error, please contact support for assistance! If you did not enter a license before please submit the provided license key.</p></center>

					</div>
				</div>
			{elseif $core.license.status eq 'expired'}
				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_product}:</p>
					</div>
					<div class="col-md-9">
						<p><strong>SoluteDNS Core</strong></p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">

						<div class="bootstrap">
							<div class="alert2 alert2-warning">
								<h4>License Expired</h4>
								<p>The license key you are using has been expired. To continue using SoluteDNS you need to renew your subscription.</p>
							</div>
						</div>

					</div>
				</div>
			{elseif $core.license.status eq 'error'}
				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_product}:</p>
					</div>
					<div class="col-md-9">
						<p><strong>SoluteDNS Core</strong></p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">

						<div class="bootstrap">
							<div class="alert2 alert2-danger">
								<h4>License Error</h4>
								<p>An error occurred when processing the license data. Please make sure all required PowerDNS tables exist.</p>
							</div>
						</div>

					</div>
				</div>
			{elseif $core.license.status eq 'database'}
				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_product}:</p>
					</div>
					<div class="col-md-9">
						<p><strong>SoluteDNS Core</strong></p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">

						<div class="bootstrap">
							<div class="alert2 alert2-warning">
								<h4>License Unavailable</h4>
								<p>Unable to retrieve license information. Is the SoluteDNS Core connected to the nameserver database?</p>
							</div>
						</div>

					</div>
				</div>
			{else}
				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_product}:</p>
					</div>
					<div class="col-md-9">
						<p><strong>{$core.license.productname}</strong></p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_status}:</p>
					</div>
					<div class="col-md-9">
						<p> {if $core.license.status eq 'Active'} <span class="label active">{$core.license.status}</span> {elseif $core.license.status eq 'Pending'} <span class="label pending">{$core.license.status}</span> {else} <span class="label closed">{$core.license.status}</span> {/if} </p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_expires}:</p>
					</div>
					<div class="col-md-9">
						<p> {if $core.license.nextduedate eq '0000-00-00'} {$LANG.admin_never} {else} {$core.license.nextduedate} {/if}</p>
					</div>
				</div>

				<div class="row spacer_15">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_licensed_to}:</p>
					</div>
					<div class="col-md-9">
						<p>{$core.license.companyname}
							<br />
							<i>{$core.license.registeredname}</i></p>
					</div>
				</div>

				<div class="row spacer_15">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_limit}:</p>
					</div>
					<div class="col-md-9">
						<p>{if $core.license.zonelimit}{if {$core.license.zonelimit} eq '0'}{$LANG.admin_system_license_unlimited}{else}{$core.license.zonelimit} {$LANG.admin_system_license_zones}{/if}{else}{$LANG.global_status_na}{/if}</p>
					</div>
				</div>

				<div class="row">
					<div class="col-md-3 text-right title">
						<p>{$LANG.admin_system_license_addons}:</p>
					</div>
					<div class="col-md-9">
						<p>
							{if $core.license.addon} {foreach from=$core.license.addon item=addon}
								<p>- {$addon.name} {if $addon.status eq 'Active'} <span class="label active">{$addon.status}</span> {elseif $license_addons.status eq 'Active'} <span class="label pending">{$addon.status}</span> {else} <span class="label">{$addon.status}</span> {/if} {$addon.duedate}
								</p>
							{/foreach} {else} {$LANG.global_status_none} {/if}
							</p>
						</div>
					</div>

					<div class="row spacer_15">
						<div class="col-md-3 text-right title">
							<p>{$LANG.admin_system_license_valid_domains}:</p>
						</div>
						<div class="col-md-9">
							<p>{if $core.license.validdomain}{$core.license.validdomain}{else}{$LANG.global_status_na}{/if}</p>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3 text-right title">
							<p>{$LANG.admin_system_license_valid_ips}:</p>
						</div>
						<div class="col-md-9">
							<p>{if $core.license.validip}{$core.license.validip}{else}{$LANG.global_status_na}{/if}</p>
						</div>
					</div>

					<div class="row">
						<div class="col-md-3 text-right title">
							<p>{$LANG.admin_system_license_valid_directory}:</p>
						</div>
						<div class="col-md-9">
							<p>{$core.license.validdirectory}</p>
						</div>
					</div>
				{/if}
			{else}
				<div class="row">
					<div class="col-md-3"></div>
					<div class="col-md-9">
						<p class="text-danger"><i>{$LANG.admin_system_core_not_loaded}</i></p>
					</div>
				</div>
			{/if}

            <div class="row spacer_15">
                <div class="col-md-3 text-right title">
                    <label for="sdns_system_license">{$LANG.admin_system_license_key}:</label>
                </div>
                <div class="col-md-4">
                    <input type="text" class="form-padding form-control" name="sdns_system_license" id="sdns_system_license" value="{Controller::config(license)}">
                </div>
                <div class="col-md-5"></div>
            </div>

            <div class="row text-center">
                <br />
                <input class="btn btn-primary" type="button" onClick="window.updateSettings('system');" value="{$LANG.admin_btn_save_changes}" />
            </div>

        </fieldset>
    </form>
