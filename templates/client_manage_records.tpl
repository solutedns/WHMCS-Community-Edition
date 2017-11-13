<div class="table-container clearfix">
	<table id="sdns_records" class="table table-list dataTable no-footer dtr-inline">
		<thead>
			<tr>
				<th></th>
				<th>{$MLANG.global_dns_name}</th>
				<th>{$MLANG.global_dns_type}</th>
				<th>{$MLANG.global_dns_content}</th>
				<th>{$MLANG.global_dns_prio}</th>
				<th>{$MLANG.global_dns_ttl}</th>
				<th><span data-toggle="modal" data-target="#dialog_addRecord">
						<button type="button" class="btn btn-success btn-xs" data-toggle="tooltip" data-placement="bottom" title="{$MLANG.admin_manage_records_addrecord}" {if $maintenance}DISABLED{/if}><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
					</span>
				</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td colspan="7" class="dataTables_empty text-center"></td>
			</tr>
		</tbody>
	</table>
	<div class="pull-right"><a class="btn btn-sm btn-default {if $maintenance}disabled{/if}" href="#" onclick="deleteSelected();"><span class="glyphicon glyphicon-remove" aria-hidden="true"></span><span class="dropmenu_desc">{$MLANG.client_manage_records_deleteselected}</span></a></div>
</div>

<input type="hidden" id="sdns_zone" value="{$domain->id}">
<input type="hidden" id="sdns_domain" value="{$domain->domain}">
<input type="hidden" id="sdns_record">

{if $maintenance eq false}
	<!-- Add Modal -->
	<div class="bootstrap">
		<div class="modal fade" id="dialog_addRecord" tabindex="-1" role="dialog" aria-labelledby="dialog_addRecord" aria-hidden="true">
			<div class="modal-dialog modal-lg">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">{$MLANG.global_head_add_record}</h4>
					</div>
					<div class="modal-body">
						<div class="row">
							<div id="sdns_z-name_0" class="col-md-3">
								<label for="sdns_name_0">{$MLANG.global_dns_name}:</label>
								<input type="text" class="form-padding form-control" name="sdns_name_0" id="sdns_name_0" placeholder="{$domain->domain}">
							</div>
							<div id="sdns_z-type_0" class="col-md-2">
								<label for="sdns_type_0">{$MLANG.global_dns_type}:</label>
								<select class="form-padding form-control" name="sdns_type_0" id="sdns_type_0">
									{foreach from=$records item=type}
										<option value="{$type}">{$type}</option>
									{/foreach}
								</select>
							</div>
							<div id="sdns_z-content_0" class="col-md-4">
								<label for="sdns_content_0">{$MLANG.global_dns_content}:</label>
								<input type="text" class="form-padding form-control" name="sdns_content_0" id="sdns_content_0">
							</div>
							<div id="sdns_z-prio_0" class="col-md-1">
								<label for="sdns_prio_0">{$MLANG.global_dns_prio}:</label>
								<input type="text" class="form-padding form-control" name="sdns_prio_0" id="sdns_prio_0">
							</div>
							<div id="sdns_z-ttl_0" class="col-md-2">
								<label for="sdns_ttl_0">{$MLANG.global_dns_ttl}:</label>
								{if $preset_ttl}
									<select class="form-padding form-control" name="sdns_ttl_0" id="sdns_ttl_0">
										<option value="60">1 {$MLANG.global_dns_minute}</option>
										<option value="300">5 {$MLANG.global_dns_minutes}</option>
										<option SELECTED value="3600">1 {$MLANG.global_dns_hour}</option>
										<option value="86400">1 {$MLANG.global_dns_day}</option>
									</select>
								{else}
									<input type="text" class="form-padding form-control" name="sdns_ttl_0" id="sdns_ttl_0">
								{/if}
							</div>
						</div>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-success" data-dismiss="modal" onclick="record_add()">{$MLANG.global_btn_add}</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">{$MLANG.global_btn_cancel}</button>
					</div>
				</div>
			</div>
		</div>
	</div>

	<!-- Delete Modal -->
	<div class="bootstrap">
		<div class="modal fade" id="dialog_deleteRecord" tabindex="-1" role="dialog" aria-labelledby="dialog_deleteRecord" aria-hidden="true">
			<div class="modal-dialog">
				<div class="modal-content">
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
						<h4 class="modal-title">{$MLANG.global_head_delete_record}</h4>
					</div>
					<div class="modal-body">
						<p>{$MLANG.global_text_delete_record}</p>
						<br />
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-danger" data-dismiss="modal" onclick="record_delete()">{$MLANG.global_btn_delete}</button>
						<button type="button" class="btn btn-default" data-dismiss="modal">{$MLANG.global_btn_cancel}</button>
					</div>
				</div>
			</div>
		</div>
	</div>
{/if}