<h2><div class="row">
        <div class="col-md-9">
            <p>{$LANG.admin_menu_template}</p>
        </div>
        <div class="col-md-3">
            <div class="text-right">
                <!-- Split button -->
                <div class="btn-group">
                    <button type="button" class="btn btn-success btn-sm" data-toggle="modal" data-target="#dialog_addRecord" onclick=""><span class="glyphicon glyphicon-plus" aria-hidden="true"></span></button>
                </div>
            </div>
        </div>
    </div></h2>


{assign var=products value=Controller::product_list()}
{assign var=records value=Controller::inConfig(record_types)}

<div class="pull-right">
	<select class="form-padding form-control" name="sdns_template" id="sdns_template" onchange="selectTemplate(value);">
		<option value="0">{$LANG.global_general_defaulttemplate}</option>
		{foreach from=$products item=product}
			<option value="{$product->id}">{$product->name}</option>
		{/foreach}
	</select>
</div>
<h3>{$LANG.global_general_records}</h3>

<table class="dataTable display" id="sdns_template_records" width="100%" border="0" cellspacing="1" cellpadding="3">
	<thead>
		<tr>
			<th></th>
			<th>{$LANG.global_dns_id}</th>
			<th>{$LANG.global_dns_name}</th>
			<th>{$LANG.global_dns_type}</th>
			<th>{$LANG.global_dns_content}</th>
			<th>{$LANG.global_dns_prio}</th>
			<th>{$LANG.global_dns_ttl}</th>
			<th></th>
		</tr>
	</thead>
	<tbody>
		<tr>
			<td colspan="8" class="dataTables_empty">{$LANG.global_general_loading}</td>
		</tr>
	</tbody>
</table>

</div>

<!-- Add Modal -->
<div class="bootstrap">
    <div class="modal fade" id="dialog_addRecord" tabindex="-1" role="dialog" aria-labelledby="dialog_addRecord" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title">{$LANG.global_head_add_record}</h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div id="sdns_z-name_0" class="col-md-3">
                            <label for="sdns_name_0">{$LANG.global_dns_name}:</label>
                            <input type="text" class="form-padding form-control" name="sdns_name_0" id="sdns_name_0" placeholder="{literal}{domain}{/literal}">
                        </div>
                        <div class="col-md-2">
                            <label for="sdns_type_0">{$LANG.global_dns_type}:</label>
                            <select class="form-padding form-control" name="sdns_type_0" id="sdns_type_0">
                                {foreach from=$records item=type}
									<option value="{$type}">{$type}</option>
                                {/foreach}
                            </select>
                        </div>
                        <div id="sdns_z-content_0" class="col-md-4">
                            <label for="sdns_content_0">{$LANG.global_dns_content}:</label>
                            <input type="text" class="form-padding form-control" name="sdns_content_0" id="sdns_content_0">
                        </div>
                        <div id="sdns_z-prio_0" class="col-md-1">
                            <label for="sdns_prio_0">{$LANG.global_dns_prio}:</label>
                            <input type="text" class="form-padding form-control" name="sdns_prio_0" id="sdns_prio_0">
                        </div>
                        <div class="col-md-2">
                            <label for="sdns_ttl_0">{$LANG.global_dns_ttl}:</label>
							{if Controller::config(preset_ttl)}
								<select class="form-padding form-control" name="sdns_ttl_0" id="sdns_ttl_0">
									<option value="60">1 {$LANG.global_dns_minute}</option>
									<option value="300">5 {$LANG.global_dns_minutes}</option>
									<option SELECTED value="3600">1 {$LANG.global_dns_hour}</option>
									<option value="86400">1 {$LANG.global_dns_day}</option>
								</select>
                            {else}
								<input type="text" class="form-padding form-control" name="sdns_ttl_0" id="sdns_ttl_0">
                            {/if}
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-success" data-dismiss="modal" onclick="record_add('template')">{$LANG.global_btn_add}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.global_btn_cancel}</button>
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
                    <h4 class="modal-title">{$LANG.global_head_delete_record}</h4>
                </div>
                <div class="modal-body">
                    <p>{$LANG.global_text_delete_record}</p>
                    <br />
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal" onclick="record_delete('template')">{$LANG.global_btn_delete}</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">{$LANG.global_btn_cancel}</button>
                </div>
            </div>
        </div>
    </div>

	<input type="hidden" id="sdns_record">