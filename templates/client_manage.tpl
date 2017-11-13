{if $domain->domain eq false}
	<div class="alert alert-danger text-center" role="alert">
		<p>{$MLANG.client_msg_access_denied_desc}</p>
	</div>
{else}
	<div id="msgConsole">
	</div>

	<div role="tabpanel"> 

		<!-- Nav Tabs -->
		<ul class="nav nav-tabs">
			<li class="title"><h4><a href="clientarea.php?action=domaindetails&id={$domain->id}">{$domain->domain|ucfirst}</a> {if $dnssec.nsec}<span class="label label-primary"><span class="glyphicons glyphicons-unlock" aria-hidden="true"></span> {$dnssec.nsec}</span></h4>{/if}
			</li>
			{if $dnssec.keys}<li class="pull-right"><a data-toggle="tab" href="#dnssec">{$MLANG.client_menu_dnssec}</a></li>{/if}
			<li class="active pull-right"><a data-toggle="tab" href="#records">{$MLANG.client_menu_records}</a></li>
		</ul>

		<!-- Tab Panes -->
		<div class="tab-content">
			<div id="records" class="tab-pane fade in active">
				<div class= "col-m23d-12">
					<!-- Records Tab -->
					{include file=".{DIRECTORY_SEPARATOR}client_manage_records.tpl"}
				</div>
			</div>
			{if $dnssec.keys}
				<div id="dnssec" class="tab-pane fade">
					<div class= "col-m3d-12">
						<!-- DNSsec Tab -->
						{include file=".{DIRECTORY_SEPARATOR}client_manage_dnssec.tpl"}
					</div>
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
{/if}