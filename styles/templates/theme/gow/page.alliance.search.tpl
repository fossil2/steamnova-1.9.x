
{block name="title" prepend}{$LNG.lm_alliance}{/block}

{block name="content"}

<form action="game.php?page=alliance&amp;mode=search" method="post">
    <table class="table table-dark table-sm table-gow fs-12 my-2">
        <tr>
            <th colspan="2" class="text-center">
                {$LNG.al_find_alliances}
            </th>
        </tr>
        <tr>
            <td>{$LNG.al_find_text}</td>
            <td>
                <input
                    type="text"
                    name="searchtext"
                    value="{$searchText|escape:'html'}"
                    class="form-control form-control-sm d-inline-block"
                    style="width: 65%;"
                >
                <input
                    type="submit"
                    value="{$LNG.al_find_submit}"
                    class="button-blue"
                >
            </td>
        </tr>
    </table>
</form>

{if !empty($searchList)}

<table class="table table-dark table-sm table-gow fs-12 my-2">
    <tr>
        <th class="text-center">{$LNG.al_ally_info_tag}</th>
        <th class="text-center">{$LNG.al_ally_info_name}</th>
        <th class="text-center">{$LNG.al_ally_info_members}</th>
    </tr>

    {foreach $searchList as $seachRow}
    <tr>
        <td class="text-center">
            <a href="game.php?page=alliance&amp;mode=apply&amp;id={$seachRow.id}">
                {$seachRow.tag|escape:'html'}
            </a>
        </td>
        <td class="text-center">
            <a href="game.php?page=alliance&amp;mode=apply&amp;id={$seachRow.id}">
                {$seachRow.name|escape:'html'}
            </a>
        </td>
        <td class="text-center">
            <a href="game.php?page=alliance&amp;mode=apply&amp;id={$seachRow.id}">
                {$seachRow.members}
            </a>
        </td>
    </tr>
    {foreachelse}
    <tr>
        <td colspan="3" class="text-center">
            {$LNG.al_find_no_alliances}
        </td>
    </tr>
    {/foreach}

</table>

{/if}

{/block}
