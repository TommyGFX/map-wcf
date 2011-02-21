<div class="formField">
	<ul class="itemList" id="classList">
	{foreach from=$customInputOptions item=opt}
		<li id="item_{$opt.optionName}">
			<div class="buttons">
				<img id="status_{$opt.optionName}" src="{@RELATIVE_WCF_DIR}icon/{if $opt.isCustomInput}enabled{else}disabled{/if}S.png" alt="" />
			</div>
			<a href="#" onclick="return changeCustomInput('{$opt.optionName}')">
				<h3 class="itemListTitle">
					{$opt.optionName}
				</h3>
			</a>
		</li>
	{/foreach}
	</ul>
</div>
