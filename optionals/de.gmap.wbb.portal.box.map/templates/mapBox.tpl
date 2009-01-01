		<div class="border" id="box{$boxID}">
			<div class="containerHead">
				<div class="containerIcon">
			    	<a href="javascript: void(0)" onclick="openList('mapbox', true)">
                	<img src="icon/minusS.png" id="birthdayboxImage" alt="" /></a>
            	</div>
				<div class="containerContent"><span>{lang}wbb.portal.box.birthday.title{/lang}</span>
				</div>
           	</div>
			<div class="container-1" id="birthdaybox">
			    <div class="containerContent">
			    {if $item.birthdaylist|count}
                {foreach from=$item.birthdaylist item=birthday}
                    <p class="smallFont"><a href="index.php?page=User&amp;userID={@$birthday.userID}{@SID_ARG_2ND}">{$birthday.username}</a>
                    {if $birthday.age != 0} ({$birthday.age}){/if}
                    </p>
                {/foreach}
            	{else}<p class="smallFont">{lang}wbb.portal.box.birthday.nobirthdays{/lang}</p>
            	{/if}
            	</div>
            </div>
        </div>
        <script type="text/javascript">
		//<![CDATA[
		initList('birthdaybox', {@$item.Status});
		//]]>
		</script>
