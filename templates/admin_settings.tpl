{assign var=records value=Controller::inConfig(record_types)}
{assign var=status value=Controller::inConfig(auto_delete)}

<h2>{$LANG.admin_menu_settings}</h2>

<form role="form" id="settings" class="label-form">
	<fieldset>
		<input type="hidden" name="sdns_form" value="settings">
		<h3>{$LANG.admin_settings_allowed_records}</h3>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_type">{$LANG.admin_settings_allowed_records}:</label>
			</div>
			<div class="col-md-9">
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_a" id="sdns_type_a" type="checkbox" {if 'A'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_a">A</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_aaaa" id="sdns_type_aaaa" type="checkbox" {if 'AAAA'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_aaaa">AAAA</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_alias" id="sdns_type_alias" type="checkbox" {if 'ALIAS'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_alias">ALIAS</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_caa" id="sdns_type_caa" type="checkbox" {if 'CAA'|in_array:$records} CHECKED{/if}>
						<label for="sdns_type_caa">CAA</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_cname" id="sdns_type_cname" type="checkbox" {if 'CNAME'|in_array:$records} CHECKED{/if}>
						<label for="sdns_type_cname">CNAME</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_hinfo" id="sdns_type_hinfo" type="checkbox" {if 'HINFO'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_hinfo">HINFO</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_mx" id="sdns_type_mx" type="checkbox" {if 'MX'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_mx">MX</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_naptr" id="sdns_type_naptr" type="checkbox" {if 'NAPTR'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_naptr">NAPTR</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_ns" id="sdns_type_ns" type="checkbox" {if 'NS'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_ns">NS</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_ptr" id="sdns_type_ptr" type="checkbox" {if 'PTR'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_ptr">PTR</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_rp" id="sdns_type_rp" type="checkbox" {if 'RP'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_rp">RP</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_soa" id="sdns_type_soa" type="checkbox" disabled>
						<label for="sdns_type_soa">SOA</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_spf" id="sdns_type_spf" type="checkbox" {if 'SPF'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_spf">SPF</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_srv" id="sdns_type_srv" type="checkbox" {if 'SRV'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_srv">SRV</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_sshfp" id="sdns_type_sshfp" type="checkbox" {if 'SSHFP'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_sshfp">SSHFP</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_tlsa" id="sdns_type_tlsa" type="checkbox" {if 'TLSA'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_tlsa">TLSA</label>
					</div>
				</div>
				<div class="col-md-2">
					<div class="checkbox">
						<input name="sdns_type_txt" id="sdns_type_txt" type="checkbox" {if 'TXT'|in_array:$records}CHECKED{/if}>
						<label for="sdns_type_txt">TXT</label>
					</div>
				</div>
			</div>
		</div>
		<hr />
		<h3>{$LANG.admin_settings_default_soa}</h3>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_hostmaster">{$LANG.admin_settings_default_hostmaster}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_hostmaster_field">
					<input type="text" class="form-padding form-control" name="sdns_soa_hostmaster" id="sdns_soa_hostmaster" value="{Controller::config(soa_hostmaster)}">
				</div>
			</div>
			<div class="col-md-6">
				<label class="info_text" for="sdns_soa_hostmaster">{$LANG.admin_settings_default_hostmaster_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right  title">
				<label for="sdns_soa_serial">{$LANG.admin_settings_default_serial}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_serial_field">
					<select class="form-padding form-control" name="sdns_soa_serial" id="sdns_soa_serial">
						<option {if Controller::config(soa_serial) eq 'default'}SELECTED{/if} value="default">{$LANG.admin_settings_record_select_default}</option>
						<option {if Controller::config(soa_serial) eq 'epoch'}SELECTED{/if} value="epoch">{$LANG.admin_settings_record_select_epoch}</option>
						<option {if Controller::config(soa_serial) eq 'zero'}SELECTED{/if} value="zero">{$LANG.admin_settings_record_select_zero}</option>
					</select>
				</div>
			</div>
			<div class="col-md-6">
				<label class="info_text" for="sdns_soa_serial">{$LANG.admin_settings_default_serial_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_refresh">{$LANG.admin_settings_default_refresh}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_refresh_field">
					<input type="text" class="form-padding form-control" name="sdns_soa_refresh" id="sdns_soa_refresh" value="{Controller::config(soa_refresh)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_retry">{$LANG.admin_settings_default_retry}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_retry_field">
					<input type="text" class="form-padding form-control" name="sdns_soa_retry" id="sdns_soa_retry" value="{Controller::config(soa_retry)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_expire">{$LANG.admin_settings_default_expire}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_expire_field">
					<input type="text" class="form-padding form-control" name="sdns_soa_expire" id="sdns_soa_expire" value="{Controller::config(soa_expire)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_ttl">{$LANG.admin_settings_default_ttl}:</label>
			</div>
			<div class="col-md-3">
				<div id="sdns_soa_ttl_field">
					<input type="text" class="form-padding form-control" name="sdns_soa_ttl" id="sdns_soa_ttl" value="{Controller::config(soa_ttl)}">
				</div>
			</div>
			<div class="col-md-6"></div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_soa_custom_primary">{$LANG.admin_settings_default_custom_primary}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(custom_primary)}checked {/if}name="sdns_soa_custom_primary" id="sdns_soa_custom_primary" type="checkbox">
					<label for="sdns_soa_custom_primary">{$LANG.admin_settings_default_custom_primary_desc}</label>
				</div>
			</div>
		</div>
		<hr />
		<h3>{$LANG.admin_settings_record_limits}</h3>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_record_limit">{$LANG.admin_settings_record_limit}:</label>
			</div>
			<div class="col-md-1">
				<div id="sdns_record_limit_field">
					<input name="sdns_record_limit" type="text" class="form-control" name="sdns_record_limit" id="sdns_record_limit" value="{Controller::config(record_limit)}">
				</div>
			</div>
			<div class="col-md-8">
				<label class="info_text" for="sdns_record_limit">{$LANG.admin_settings_record_limit_desc}</label>
			</div>
		</div>
		<hr />
		<h3>{$LANG.admin_settings_accessibility}</h3>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_respect_registrar">{$LANG.admin_settings_respect_registrar}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(respect_registrar)}checked {/if}name="sdns_respect_registrar" id="sdns_respect_registrar" type="checkbox">
					<label for="sdns_respect_registrar">{$LANG.admin_settings_respect_registrar_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_hide_soa">{$LANG.admin_settings_force_dns}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(force_dns)}checked {/if}name="sdns_force_dns" id="sdns_force_dns" type="checkbox">
					<label for="sdns_force_dns">{$LANG.admin_settings_force_dns_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_hide_soa">{$LANG.admin_settings_hide_soa}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(hide_soa)}checked {/if}name="sdns_hide_soa" id="sdns_hide_soa" type="checkbox">
					<label for="sdns_hide_soa">{$LANG.admin_settings_hide_soa_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_disable_ns">{$LANG.admin_settings_disable_ns}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(disable_ns)}checked {/if}name="sdns_disable_ns" id="sdns_disable_ns" type="checkbox">
					<label for="sdns_disable_ns">{$LANG.admin_settings_disable_ns_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_preset_ttl">{$LANG.admin_settings_preset_ttl}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(preset_ttl)}checked {/if}name="sdns_preset_ttl" id="sdns_preset_ttl" type="checkbox">
					<label for="sdns_preset_ttl">{$LANG.admin_settings_preset_ttl_desc}</label>
				</div>
			</div>
		</div>
		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_dns_pagination">{$LANG.admin_settings_paging}:</label>
			</div>
			<div class="col-md-9">
				<div id="sdns_dns_pagination_field" class="checkbox chx_label">
					<input {if Controller::config(dns_pagination)}checked {/if}name="sdns_dns_pagination" id="sdns_dns_pagination" type="checkbox">
					<label for="sdns_dns_pagination">{$LANG.admin_settings_paging_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_client_urlrewrite">{$LANG.admin_settings_url_rewrite}:</label>
			</div>
			<div class="col-md-2 info_text">
				<div id="sdns_client_urlrewrite_field">
					<input type="text" class="form-padding form-control" name="sdns_client_urlrewrite" id="sdns_client_urlrewrite" value="{Controller::config(client_urlrewrite)}">
				</div>
			</div>
			<div class="col-md-7 info_text">
				<label class="info_text" for="sdns_client_urlrewrite">{$LANG.admin_settings_url_rewrite_desc}</label>
			</div>
		</div>
		<hr />
		<h3>{$LANG.admin_settings_automation}</h3>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_auto_create">{$LANG.admin_settings_auto_create}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox checkbox-success chx_label">
					<input {if Controller::config(auto_create)}checked {/if}name="sdns_auto_create" id="sdns_auto_create" type="checkbox">
					<label for="sdns_auto_create">{$LANG.admin_settings_auto_create_desc}</label>
				</div>
			</div>
		</div>
		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_auto_delete">{$LANG.admin_settings_auto_delete}:</label>
			</div>
			<div class="col-md-9">
				<label class="info_text" for="sdns_soa_hostmaster">{$LANG.admin_settings_auto_delete_desc}</label>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title"></div>
			<div class="col-md-9">
				<div class="row">
					<div class="col-md-2">
						<div class="checkbox checkbox-danger chx_label">
							<input {if 'cancelled'|in_array:$status} CHECKED{/if} name="sdns_deletestate_cancelled" id="sdns_deletestate_cancelled" type="checkbox">
							<label for="sdns_deletestate_cancelled">{Lang::trans(domainsCancelled)}</label>
						</div>
					</div>
					<div class="col-md-2">
						<div class="checkbox checkbox-danger chx_label">
							<input {if 'expired'|in_array:$status} CHECKED{/if} name="sdns_deletestate_expired" id="sdns_deletestate_expired" type="checkbox">
							<label for="sdns_deletestate_expired">{Lang::trans(domainsExpired)}</label>
						</div>
					</div>
					<div class="col-md-2">
						<div class="checkbox checkbox-danger chx_label">
							<input {if 'fraud'|in_array:$status} CHECKED{/if} name="sdns_deletestate_fraud" id="sdns_deletestate_fraud" type="checkbox">
							<label for="sdns_deletestate_fraud">{Lang::trans(domainsFraud)}</label>
						</div>
					</div>
					<div class="col-md-2">
						<div class="checkbox checkbox-danger chx_label">
							<input {if 'transferredaway'|in_array:$status} CHECKED{/if} name="sdns_deletestate_transferredaway" id="sdns_deletestate_transferredaway" type="checkbox">
							<label for="sdns_deletestate_transferredaway">{Lang::trans(domainsTransferredAway)}</label>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title"></div>
			<div class="col-md-9">
				<div class="checkbox checkbox-danger chx_label">
					<input {if Controller::config(auto_delete_whmcs)}checked {/if}name="sdns_auto_delete_whmcs" id="sdns_auto_delete_whmcs" type="checkbox">
					<label for="sdns_auto_delete_whmcs">{$LANG.admin_settings_auto_delete_remove}</label>
				</div>
			</div>
		</div>
		<div class="row spacer_15">
			<div class="col-md-3 text-right title">
				<label for="sdns_auto_enable">{$LANG.admin_settings_auto_dns_management}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(auto_enabled)}checked {/if}name="sdns_auto_enable" id="sdns_auto_enable" type="checkbox">
					<label for="sdns_auto_enable">{$LANG.admin_settings_auto_dns_management_desc}</label>
				</div>
			</div>
		</div>
		<div class="row">
			<div class="col-md-3 text-right title">
				<label for="sdns_auto_todo">{$LANG.admin_settings_auto_todo}:</label>
			</div>
			<div class="col-md-9">
				<div class="checkbox chx_label">
					<input {if Controller::config(auto_todo)}checked {/if}name="sdns_auto_todo" id="sdns_auto_todo" type="checkbox">
					<label for="sdns_auto_todo">{$LANG.admin_settings_auto_todo_desc}</label>
				</div>
			</div>
		</div>
		<div class="row text-center"> <br />
			<input class="btn btn-primary" type="button" onclick="window.updateSettings('settings');" value="{$LANG.admin_btn_save_changes}" />
		</div>
	</fieldset>
</form>
