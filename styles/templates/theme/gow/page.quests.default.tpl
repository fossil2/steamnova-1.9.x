{block name="title" prepend}{$LNG.quest_21}{/block}

{block name="content"}

<script>
window.questTranslations = {
	finish: "{$LNG.quest_41|escape:'javascript'}"
};
</script>

<div class="content_page quest_page">

	<div class="title quest_page_title">
		{$LNG.quest_21}
	</div>

	<div class="quests">

		<div class="categories">
			<ul id="onglets">
				{foreach from=$result_cat_list item=categories}
					<li value="{$categories.id_cat}">
						{$categories.name_cat}
					</li>
				{/foreach}
			</ul>
		</div>

		<div class="quest_content" id="contents"></div>

	</div>

</div>

{/block}