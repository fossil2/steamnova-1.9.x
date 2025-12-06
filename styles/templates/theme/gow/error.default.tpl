{block name="title" prepend}{$LNG.fcm_info}{/block}
{block name="content"}
 <table class="table table-dark table-sm fs-12 table-gow">
	<tr>
		<th>{$LNG.fcm_info}</th>
	</tr>
	<tr>
		<td>
			<p>{$message}</p>
			{if !empty($redirectButtons)}
			<p>
				{foreach $redirectButtons as $button}
				{if isset($button.url) && $button.label}
					<a href="{$button.url}">
						<button class="text-yellow fs-12">{$button.label}</button>
					</a>
				{/if}
				{/foreach}
			</p>
			{/if}
		</td>
	</tr>
</table>
{/block}
