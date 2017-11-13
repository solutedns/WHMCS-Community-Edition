<div id="msgConsole">
</div>

<div role="tabpanel"> 

	<!-- Nav Tabs -->
	<ul class="nav nav-tabs">
		<li class="pull-right"><a target="_blank" href="https://docs.solutedns.com/whmcs/community-edition/getting-started/"><span class="glyphicon glyphicon-question-sign text-primary" aria-hidden="true"></span></a></li>
		<li class="active"><a data-toggle="tab" href="#template">{$LANG.admin_menu_template}</a></li>
		<li><a data-toggle="tab" href="#settings">{$LANG.admin_menu_settings}</a></li>
		<li><a data-toggle="tab" href="#nameserver">{$LANG.admin_menu_nameserver}</a></li>
		<li><a data-toggle="tab" href="#system">{$LANG.admin_menu_system}</a></li>
	</ul>

	<!-- Tab Panes -->
	<div class="tab-content">
		<div id="template" class="tab-pane fade in active">
			<div class= "col-md-12"> {include file="{$base_path}/templates/admin_template.tpl"}</div>
		</div>
		<div id="settings" class="tab-pane fade">
			<div class= "col-md-12"> {include file="{$base_path}/templates/admin_settings.tpl"}</div>
		</div>
		<div id="nameserver" class="tab-pane fade">
			<div class= "col-md-12"> {include file="{$base_path}/templates/admin_nameserver.tpl"}</div>
		</div>
		<div id="system" class="tab-pane fade">
			<div class= "col-md-12"> {include file="{$base_path}/templates/admin_system.tpl"}</div>
		</div>
	</div>
</div>

<script>
	getState();

	$(document).ready(function () {
		drawRecords('sdns_template_records');
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
