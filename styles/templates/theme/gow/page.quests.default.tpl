{block name="title" prepend}Quêtes{/block}
{block name="content"}
 <script src="translation.js.php"></script>
  <script src="quests.js"></script>

<div class="content_page" style="background-color: #333333; padding: 10px; border: 1px solid #555555; border-radius: 10px;">
	<div class="title">
		{$LNG.quest_21}
	</div>

	<div class="quests">
		<div class="categories">
            <ul id="onglets">
                {foreach from=$result_cat_list item=categories}
                    <li value="{$categories.id_cat}">{$categories.name_cat}</li>
                {/foreach}
            </ul>
        </div>

        <div class="quest_content" id="contents" style="background-color: #333333; padding: 10px; border: 1px solid #555555; border-radius: 10px;">
            {if isset($quests)}
                {foreach from=$quests item=quest}
                    <div class="quest">
                        <h2>{$quest.name}</h2>
                        <p>{$quest.description}</p>
                    </div>
                {/foreach}
            {/if}
        </div>
	</div>
</div>
{/block}