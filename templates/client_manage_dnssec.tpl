<h5>{$MLANG.global_general_dnssec_keys}</h5>
<table width="100%" class="table table-list dataTable no-footer dtr-inline" border="0" cellspacing="1" cellpadding="3">
	<tr>
		<th align="center" width="11%"><strong>{$MLANG.global_dns_keytag}</strong></th>
		<th align="center" width="17%"><strong>{$MLANG.global_dns_algorithm}</strong></th>
		<th align="center" width="8%"><strong>{$MLANG.global_dns_flag}</strong></th>
		<th align="center" width="9%"><strong>{$MLANG.global_dns_status}</strong></th>
		<th align="center" width="55%"><strong>{$MLANG.global_dns_publickey}</strong></th>
	</tr>
	{if $dnssec.keys}
        {foreach from=$dnssec.keys item=key}
			<tr>
				<td align="center">
					{$key.key_tag}
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
						{$MLANG.global_general_unknown}
					{/if}
				</td>
				<td align="center">
					{$key.flag}
				</td>
				<td align="center">
					{if $key.active eq '1'}
						<span class="label status status-active">active</span>
					{else}
						<span class="label status">inactive</span>
					{/if}                
				</td>      
				<td align="center" style="padding-right: 8px;">
					<small><textarea style="width: 100%;">{$key.public_key}</textarea></small>
				</td>

			</tr>
        {/foreach}
	{else}
		<tr>
			<td colspan="7" class="dataTables_empty text-center">{$MLANG.global_status_noneavailable}</td>
		</tr>
	{/if}
</table>

<br />
<h5>{$MLANG.global_general_dnssec_ds}</h5>

<table width="100%" class="table table-list dataTable no-footer dtr-inline" border="0" cellspacing="1" cellpadding="3">
	<tr>
		<th align="center" width="11%"><strong>{$MLANG.global_dns_keytag}</strong></th>
		<th align="center" width="17%"><strong>{$MLANG.global_dns_algorithm}</strong></th>
		<th align="center" width="17%"><strong>{$MLANG.global_dns_digesttype}</strong></th>
		<th align="center" width="55%"><strong>{$MLANG.global_dns_digest}</strong></th>
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
						{$MLANG.admin_manage_unknown}
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
						{$MLANG.admin_manage_unknown}
					{/if}
				</td>
				<td align="center" style="padding-right: 8px;">
					<small><textarea style="width: 100%;">{$ds.digest}</textarea></small>
				</td>
			</tr>
        {/foreach}
	{else}
		<tr>
			<td colspan="5" class="dataTables_empty text-center">{$MLANG.global_status_noneavailable}</td>
		</tr>
	{/if}
</table>