<div class="row spacer_10">
	<div class="col-md-9">
		<p><strong>{$LANG.admin_manage_title_zone}:</strong>
			<br /><a href="clientsdomains.php?userid={$domain->userid}&id={$domain->id}">{$domain->domain}</a> {if $dnssec.nsec}<span class="label inactive"><span class="glyphicons glyphicons-unlock" aria-hidden="true"></span> {$dnssec.nsec}</span>{/if}</p>
	</div>
	<div class="col-md-3">
		<div class="text-right">
			<!-- Split button -->
			<div class="btn-group">
				<button type="button" class="btn btn-warning btn-sm" onclick="dnssec('rectify', '{$domain->id}');" data-toggle="tooltip" data-placement="bottom" title="{$LANG.admin_manage_dnssec_rectify}" {if Controller::config(maintenance)}DISABLED{/if}><span class="glyphicon glyphicon-flash" aria-hidden="true"></span></button>
			</div>

			<!-- Single button -->
			<div class="btn-group">
				<button type="button" class="btn btn-default btn-sm dropdown-toggle" data-toggle="dropdown" aria-expanded="false" {if Controller::config(maintenance)}DISABLED{/if}><span class="caret"></span></button>
				<ul class="dropdown-menu dropdown-menu-right" role="menu">
					<li><a href="javascript:void(0);" data-toggle="modal" data-target="#dialog_addDNSsec" onclick=""><span class="glyphicon glyphicon-plus" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_add}</span></a></li>
					<li class="divider"></li>
					{if $dnssec.nsec eq 'NSEC'}<li><a href="javascript:void(0);" onclick="dnssec('nsec3', '{$domain->id}');"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_nsec3}</span></a></li>
					{else}<li><a href="javascript:void(0);" onclick="dnssec('nsec', '{$domain->id}');"><span class="glyphicon glyphicon-lock" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_nsec}</span></a></li>{/if}
					<li><a href="javascript:void(0);" onclick="dnssec('reload', '{$domain->id}');"><span class="glyphicon glyphicon-repeat" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_reload}</span></a></li>
					<li><a href="javascript:void(0);" onclick="dnssec('reset', '{$domain->id}');"><span class="glyphicon glyphicon-refresh" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_reset}</span></a></li>
					<li class="divider"></li>
					<li><a href="javascript:void(0);" onclick="dnssec('unset', '{$domain->id}');"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="dropmenu_desc">{$LANG.admin_manage_dnssec_unset}</span></a></li>
				</ul>
			</div>


		</div>
	</div>
</div>

<h2>{$LANG.global_general_dnssec_keys}</h2>

<table width="100%" class="datatable" border="0" cellspacing="1" cellpadding="3">
	<tr>
		<th align="center" width="5%"><strong>{$LANG.global_dns_id}</strong></th>
		<th align="center" width="10%"><strong>{$LANG.global_dns_keytag}</strong></th>
		<th align="center" width="5%"><strong>{$LANG.global_dns_flag}</strong></th>
		<th align="center" width="15%"><strong>{$LANG.global_dns_algorithm}</strong></th>
		<th align="center" width="40%"><strong>{$LANG.global_dns_publickey}</strong></th>
		<th align="center" width="10%"><strong>{$LANG.global_dns_status}</strong></th>
		<th align="center" width="15%"><strong></strong></th>
	</tr>
	{if $dnssec.keys}
        {foreach from=$dnssec.keys item=key}
			<tr>
				<td align="center">
					{$key.id}
				</td>
				<td align="center">
					{$key.key_tag}
				</td>
				<td align="center">
					{$key.flag}
				</td>
				<td align="center">
					{if $key.algorithm eq '1'}
						RSA/MD5 (1)
					{elseif $key.algorithm eq '2'}
						Diffie-Hellman (2)
					{elseif $key.algorithm eq '3'}
						DSA/SHA1 (3)
					{elseif $key.algorithm eq '5'}
						RSA/SHA-1 (5)
					{elseif $key.algorithm eq '6'}
						DSA-NSEC3-SHA1 (6)
					{elseif $key.algorithm eq '7'}
						RSASHA1-NSEC3-SHA1 (7)
					{elseif $key.algorithm eq '8'}
						RSA/SHA-256 (8)
					{elseif $key.algorithm eq '10'}
						RSA/SHA-512 (10)
					{elseif $key.algorithm eq '12'}
						GOST R 34.10-2001 (12)
					{elseif $key.algorithm eq '13'}
						ECDSA Curve P-256 with SHA-256 (13)
					{elseif $key.algorithm eq '14'}
						ECDSA Curve P-384 with SHA-384 (14)
					{else}
						{$LANG.global_general_unknown}
					{/if}
				</td>
				<td align="center" style="padding-right: 8px;">
					<small><textarea style="width: 100%;">{$key.public_key}</textarea></small>
				</td>
				<td align="center">
					{if $key.active eq '1'}
						<span class="label active">active</span>
					{else}
						<span class="label label-default">inactive</span>
					{/if}                
				</td>
				<td align="center">
					{if $key.active eq '0'}
						<button type="button" onclick="dnssec('activatekey', '{$domain->id}', '{$key.id}');" value='Activate' class="btn btn-success" {if Controller::config(maintenance)}DISABLED{/if}><span class="glyphicon glyphicon-fire" aria-hidden="true"></span></button>
						<button type="button" onclick="dnssec('deletekey', '{$domain->id}', '{$key.id}');" value="Delete" class="btn btn-danger" {if Controller::config(maintenance)}DISABLED{/if}><span class="glyphicon glyphicon-remove" aria-hidden="true"></span></button>
						{else}
						<button type="button" onclick="dnssec('deactivatekey', '{$domain->id}', '{$key.id}');" value='Deactivate' class="btn btn-default" {if Controller::config(maintenance)}DISABLED{/if}><span class="glyphicon glyphicon-minus" aria-hidden="true"></span></button>
						{/if}
				</td>


			</tr>
        {/foreach}
	{else}
		<tr>
			<td colspan="7" class="dataTables_empty text-center">{$LANG.global_status_noneavailable}</td>
		</tr>
	{/if}
</table>

<br />
<h2>{$LANG.global_general_dnssec_ds}</h2>

<table width="100%" class="datatable" border="0" cellspacing="1" cellpadding="3">
	<tr>
		<th align="center" width="11%"><strong>{$LANG.global_dns_keytag}</strong></th>
		<th align="center" width="17%"><strong>{$LANG.global_dns_algorithm}</strong></th>
		<th align="center" width="17%"><strong>{$LANG.global_dns_digesttype}</strong></th>
		<th align="center" width="55%"><strong>{$LANG.global_dns_digest}</strong></th>
	</tr>
	{if $dnssec.ds}
        {foreach from=$dnssec.ds item=ds}
			<tr>
				<td align="center">
					{$ds.key_tag}
				</td>
				<td align="center">
					{if $ds.algorithm eq '1'}
						RSA/MD5 (1)
					{elseif $ds.algorithm eq '2'}
						Diffie-Hellman (2)
					{elseif $ds.algorithm eq '3'}
						DSA/SHA1 (3)
					{elseif $ds.algorithm eq '5'}
						RSA/SHA-1 (5)
					{elseif $ds.algorithm eq '6'}
						DSA-NSEC3-SHA1 (6)
					{elseif $ds.algorithm eq '7'}
						RSASHA1-NSEC3-SHA1 (7)
					{elseif $ds.algorithm eq '8'}
						RSA/SHA-256 (8)
					{elseif $ds.algorithm eq '10'}
						RSA/SHA-512 (10)
					{elseif $ds.algorithm eq '12'}
						GOST R 34.10-2001 (12)
					{elseif $ds.algorithm eq '13'}
						ECDSA Curve P-256 with SHA-256 (13)
					{elseif $ds.algorithm eq '14'}
						ECDSA Curve P-384 with SHA-384 (14)
					{else}
						{$LANG.admin_manage_unknown}
					{/if}
				</td>
				<td align="center">
					{if $ds.digest_type eq '1'}
						SHA-1 (1)
					{elseif $ds.digest_type  eq '2'}
						SHA-256 (2)
					{elseif $ds.digest_type  eq '3'}
						GOST R 34.11-94 (3)
					{elseif $ds.digest_type  eq '4'}
						SHA-384 (4)
					{else}
						{$LANG.admin_manage_unknown}
					{/if}
				</td>
				<td align="center" style="padding-right: 8px;">
					<small><textarea style="width: 100%;">{$ds.digest}</textarea></small>
				</td>
			</tr>
        {/foreach}
	{else}
		<tr>
			<td colspan="5" class="dataTables_empty text-center">{$LANG.global_status_noneavailable}</td>
		</tr>
	{/if}
</table>

<!-- Add Modal -->
<div class="bootstrap">
    <div class="modal fade" id="dialog_addDNSsec" tabindex="-1" role="dialog" aria-labelledby="dialog_addDNSsec" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$LANG.admin_manage_dnssec_add}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="sdns_z-name_0" class="col-md-3">
                            <label for="sdns_dnssec_flag">{$LANG.global_dns_flag}:</label>
                            <select name="sdns_dnssec_flag" id="sdns_dnssec_flag" class="form-padding form-control">
                                <option value="KSK">KSK</option>
                                <option value="ZSK">ZSK</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="sdns_dnssec_bits">{$LANG.global_dns_bits}:</label>
                            <select name="sdns_dnssec_bits" id="sdns_dnssec_bits" class="form-padding form-control">
                                <option value="256">256</option>
                                <option value="512">512</option>
                                <option value="1024">1024</option>
                                <option selected value="2048">2048</option>
                            </select>
                        </div>
                        <div id="sdns_z-content_0" class="col-md-6">
                            <label for="sdns_dnssec_algorithm">{$LANG.global_dns_algorithm}:</label>
                            <select name="sdns_dnssec_algorithm" id="sdns_dnssec_algorithm" class="form-padding form-control">
                                <option value="rsasha1">RSA-SHA1 (5)</option>
                                <option value="rsasha256">RSA-SHA256 (8)</option>
                                <option selected value="rsasha512">RSA-SHA512 (10)</option>
                                <option value="gost">GOST R 34.10-2001 (12)</option>
                                <option value="ecdsa256">EC-DSA-256 (13)</option>
                                <option value="ecdsa384">EC-DSA-384 (14)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal" onclick="dnssec_addkey('{$domain->id}');">{$LANG.global_btn_add}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.global_btn_cancel}</button>
                </div>
            </div>
        </div>
    </div>
</div>