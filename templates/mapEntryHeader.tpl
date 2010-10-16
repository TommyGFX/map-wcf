{assign var=activeMenuItem value=$gmapmenu->getActiveMenuItem()}

{if $gmapmenu->getMenuItems('')|count > 1}
	<div id="profileContent" class="tabMenu">
		<ul>
			{foreach from=$gmapmenu->getMenuItems('') item=item}
				<li{if $item.menuItem|in_array:$gmapmenu->getActiveMenuItems()} class="activeTabMenu"{/if}><a href="{$item.menuItemLink}">{if $item.menuItemIconM}<img src="{icon}{$item.menuItemIconM}{/icon}" alt="" /> {/if}<span>{lang}{@$item.menuItem}{/lang}</span></a></li>
			{/foreach}
		</ul>
	</div>

	<div class="subTabMenu">
		<div class="containerHead">
			{if $activeMenuItem && $gmapmenu->getMenuItems($activeMenuItem)|count}
				<ul>
					{foreach from=$gmapmenu->getMenuItems($activeMenuItem) item=item}
						<li{if $item.menuItem|in_array:$gmapmenu->getActiveMenuItems()} class="activeSubTabMenu"{/if}><a href="{$item.menuItemLink}">{if $item.menuItemIconM}<img src="{icon}{$item.menuItemIconM}{/icon}" alt="" /> {/if}<span>{lang}{@$item.menuItem}{/lang}</span></a></li>
					{/foreach}
				</ul>
			{else}
				<div> </div>
			{/if}
		</div>
	</div>
{/if}
