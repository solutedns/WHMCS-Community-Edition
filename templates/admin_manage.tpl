<div id="msgConsole">
</div>

<div role="tabpanel"> 

	<!-- Nav Tabs -->
	<ul class="nav nav-tabs">
		<li class="pull-right"><a target="_blank" href="https://docs.solutedns.com/whmcs/community-edition/getting-started/"><span class="glyphicon glyphicon-question-sign text-primary" aria-hidden="true"></span></a></li>
		<li class="active"><a data-toggle="tab" href="#records">{$LANG.admin_menu_records}</a></li>
		{if Controller::ns_details(dnssec_enable)}
			<li><a data-toggle="tab" href="#dnssec">{$LANG.admin_menu_dnssec}</a></li>
		{/if}
	</ul>

	<!-- Tab Panes -->
	<div class="tab-content">
		<div id="records" class="tab-pane fade in active">
			<div class= "col-md-12"> {include file="{$base_path}/templates/admin_manage_records.tpl"}</div>
		</div>
		{if Controller::ns_details(dnssec_enable)}
			<div id="dnssec" class="tab-pane fade">
				<div class= "col-md-12"> {include file="{$base_path}/templates/admin_manage_dnssec.tpl"}</div>
			</div>
		{/if}
	</div>
</div>

<script type="text/javascript">
	getState();

	$(document).ready(function () {
		drawRecords('sdns_records', '{$domain->id}');
	});
</script>

<noscript>
<div class="alert2 alert2-danger">
	<h4>{$LANG.nojavascript_title}</h4>
	<p>{$LANG.nojavascript_desc}</p>
</div>
<style>
	.tab-content { display:none; } .nav { display:none; }
</style>
</noscript>
