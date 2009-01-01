<script type="text/javascript">
//<![CDATA[
document.getElementById('map_api_page_urlsDiv').style.display = 'none';
//]]>
</script>
{if $page_urls|isset}
<fieldset>
	<legend>{lang}wcf.acp.option.category.map.pageurls{/lang}</legend>
	<p class="description">{lang}wcf.acp.option.category.map.pageurls.description{/lang}</p>

	{foreach from=$page_urls item=item key=key}
		<div id="map_pageurl{$key}Div" class="formElement">
			<div class="formFieldLabel">
				<label for="map_pageurl{$key}">{$key}</label>
			</div>
			<div class="formField">
				<input id="map_pageurl{$key}" type="text" class="inputText" name="values[pageurl][{$key}]" value="{$item}" />
			</div>
		</div>
	{/foreach}

	<script type="text/javascript">
	//<![CDATA[
	inlineHelp.register('map_pageurl{$key}');
	//]]>
	</script>
</fieldset>
{/if}
