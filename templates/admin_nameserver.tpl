<h2>{$LANG.admin_menu_nameserver}</h2>

<div id="ns{$ns_details.id}_msgbox" style="display: none;">
	<div id="ns{$ns_details.id}_title" style="font-weight: bold;"><h4>{$LANG.admin_nameservers_error}</h4></div>
	<div id="ns{$ns_details.id}_msg"></div>
</div>

<form role="form" id="nameserver" class="label-form">
	<fieldset>
		<input type="hidden" name="sdns_form" value="nameserver">

		<h3>{$LANG.admin_nameserver_database_details} <input class="btn btn-sm btn-default" type="button" onClick="syscheck('db', );" value="{$LANG.admin_btn_check}" /></h3>

		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_db_host">{$LANG.admin_nameserver_host}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_db_host_field">
					<input type="text" class="form-padding form-control" name="sdns_db_host" id="sdns_db_host" value="{Controller::ns_details(db_host)}">
				</div>
			</div>
			<div class="col-md-6 title">
				<label class="info_text" for="sdns_db_host">{$LANG.admin_nameserver_host_db_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_db_port">{$LANG.admin_nameserver_port}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_db_port_field">
					<input type="text" class="form-padding form-control" name="sdns_db_port" id="sdns_db_port" placeholder="3306" {if Controller::ns_details(db_port) neq '0'}value="{Controller::ns_details(db_port)}"{/if}>
				</div>
			</div>
			<div class="col-md-6 title">
				<label class="info_text" for="sdns_db_port">{$LANG.admin_nameserver_port_desc} 3306.</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_db_user">{$LANG.admin_nameserver_user}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_db_user_field">
					<input type="text" class="form-padding form-control" name="sdns_db_user" id="sdns_db_user" value="{Controller::ns_details(db_user)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_db_password">{$LANG.admin_nameserver_password}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_db_password_field">
                    <input type="password" class="form-padding form-control" name="sdns_db_password" id="sdns_db_password" placeholder="Password" value="{Controller::decrypt(Controller::ns_details(db_pass))}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_db_database">{$LANG.admin_nameserver_database}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_db_database_field">
					<input type="text" class="form-padding form-control" name="sdns_db_database" id="sdns_db_database" value="{Controller::ns_details(db_name)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_zone_type">{$LANG.admin_nameserver_zone_type}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_zone_type_field">
					<select class="form-padding form-control" name="sdns_zone_type" id="sdns_zone_type">
						<option {if Controller::ns_details(zone_type) eq 'master'}selected {/if}value="master">Master</option>
						<option {if Controller::ns_details(zone_type) eq 'native'}selected {/if}value="native">Native</option>
					</select>
				</div>
			</div>
			<div class="col-md-6 title">
				<label class="info_text" for="sdns_zone_type">{$LANG.admin_nameserver_zone_type_desc}</label>
			</div>
		</div>

		<hr />
		<h3>{$LANG.admin_nameserver_nameservers}</h3>

		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns0">{$LANG.admin_nameserver_nameserver} 1:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns0_field">
					<input type="text" class="form-padding form-control" name="sdns_ns0" id="sdns_ns0" value="{Controller::ns_details(ns0)}">
				</div>
			</div>
			<div class="col-md-6 title">
				<label class="info_text" for="sdns_ns0">{$LANG.admin_nameserver_nameserver_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns1">{$LANG.admin_nameserver_nameserver} 2:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns1_field">
					<input type="text" class="form-padding form-control" name="sdns_ns1" id="sdns_ns1" value="{Controller::ns_details(ns1)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns2">{$LANG.admin_nameserver_nameserver} 3:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns2_field">
					<input type="text" class="form-padding form-control" name="sdns_ns2" id="sdns_ns2" value="{Controller::ns_details(ns2)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns3">{$LANG.admin_nameserver_nameserver} 4:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns3_field">
					<input type="text" class="form-padding form-control" name="sdns_ns3" id="sdns_ns3" value="{Controller::ns_details(ns3)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns4">{$LANG.admin_nameserver_nameserver} 5:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns4_field">
					<input type="text" class="form-padding form-control" name="sdns_ns4" id="sdns_ns4" value="{Controller::ns_details(ns4)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ns5">{$LANG.admin_nameserver_nameserver} 6:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ns5_field">
					<input type="text" class="form-padding form-control" name="sdns_ns5" id="sdns_ns5" value="{Controller::ns_details(ns5)}">
				</div>
			</div>
			<div class="col-md-6 title">
				<label class="info_text" for="sdns_ns5">{$LANG.admin_nameserver_nameserver_leave_empty_desc}</label>
			</div>
		</div>

		<hr />
		<h3>{$LANG.admin_nameserver_ssh_details} <input class="btn btn-sm btn-default" type="button" onClick="syscheck('ssh');" value="{$LANG.admin_btn_check}" /></h3>

		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ssh_host">{$LANG.admin_nameserver_host}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ssh_host_field">
					<input type="text" class="form-padding form-control" name="sdns_ssh_host" id="sdns_ssh_host" value="{Controller::ns_details(ssh_host)}">
				</div>
			</div>
			<div class="col-md-6">
				<label class="info_text" for="sdns_ssh_host">{$LANG.admin_nameserver_host_ssh_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ssh_port">{$LANG.admin_nameserver_port}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ssh_port_field">
					<input type="text" class="form-padding form-control" name="sdns_ssh_port" id="sdns_ssh_port" placeholder="22" {if Controller::ns_details(ssh_port) neq '0'}value="{Controller::ns_details(ssh_port)}"{/if} >
				</div>
			</div>
			<div class="col-md-6">
				<label class="info_text" for="sdns_ssh_port">{$LANG.admin_nameserver_port_desc}  22.</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ssh_user">{$LANG.admin_nameserver_user}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ssh_user_field">
					<input type="text" class="form-padding form-control" name="sdns_ssh_user" id="sdns_ssh_user" value="{Controller::ns_details(ssh_user)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ssh_password">{$LANG.admin_nameserver_password}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_ssh_password_field">
					<input type="password" class="form-padding form-control" name="sdns_ssh_password" id="sdns_ssh_password" value="{Controller::decrypt(Controller::ns_details(ssh_pass))}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_ssh_key">{$LANG.admin_nameserver_private_key}:</label>
			</div>
			<div class="col-md-9">
				<div id="sdns_ssh_key_field">
					<textarea class="form-padding form-control form-100" name="sdns_ssh_key" id="sdns_ssh_key" placeholder="## ENCRYPTED DATA ##"></textarea>
					<label class="info_text" for="sdns_ssh_key">{$LANG.admin_nameserver_private_key_desc}</label>
				</div>
			</div>
		</div>

		<hr />
		<h3>{$LANG.admin_nameserver_dnssec_options}</h3>

		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_pdnsversion">{$LANG.admin_nameserver_pdns_version}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_pdnsversion_field">
					<select class="form-padding form-control" name="sdns_pdnsversion" id="sdns_pdnsversion">
						<option {if Controller::ns_details(version) eq '3'}selected {/if}value="3">3.x</option>
						<option {if Controller::ns_details(version) eq '4'}selected {/if}value="4">4.x</option>
					</select>
				</div>
			</div>
		</div>

		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_dnssec_enable">{$LANG.admin_nameserver_enable_dnssec}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::ns_details(dnssec_enable)}checked {/if}name="sdns_dnssec_enable" id="sdns_dnssec_enable" type="checkbox">
					<label for="sdns_dnssec_enable"> {$LANG.admin_nameserver_enable_dnssec_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_dnssec_rectify">{$LANG.admin_nameserver_auto_rectify}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::ns_details(dnssec_rectify)}checked {/if}name="sdns_dnssec_rectify" id="sdns_dnssec_rectify" type="checkbox">
					<label for="sdns_dnssec_rectify"> {$LANG.admin_nameserver_auto_rectify_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_dnssec_auto">{$LANG.admin_nameserver_auto_dnssec}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::ns_details(dnssec_auto)}checked {/if}name="sdns_dnssec_auto" id="sdns_dnssec_auto" type="checkbox">
					<label for="sdns_dnssec_auto"> {$LANG.admin_nameserver_auto_dnssec_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_dnssec_nsec3">{$LANG.admin_nameserver_set_nsec3}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::ns_details(dnssec_nsec3)}checked {/if}name="sdns_dnssec_nsec3" id="sdns_dnssec_nsec3" type="checkbox">
					<label for="sdns_dnssec_nsec3"> {$LANG.admin_nameserver_set_nsec3_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_dnssec_client">{$LANG.admin_nameserver_show_client}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::ns_details(dnssec_client)}checked {/if}name="sdns_dnssec_client" id="sdns_dnssec_client" type="checkbox">
					<label for="sdns_dnssec_client"> {$LANG.admin_nameserver_show_client_desc}</label>
				</div>
			</div>
		</div>

		<div class="row text-center">
			<br />
			<input class="btn btn-primary" type="button" onClick="window.updateSettings('nameserver');" value="{$LANG.admin_btn_save_changes}" />
		</div>
	</fieldset>
</form>