
{block name="title" prepend}{$LNG.lm_alliance}{/block}

{block name="content"}

<form action="game.php?page=alliance&amp;mode=admin&amp;action=membersSave" method="post">

    <div class="table-responsive">

        <table id="memberList" class="tablesorter table table-dark table-sm table-gow fs-12 my-2">

            <thead>
                <tr>
                    <th colspan="9" class="text-center">
                        {$al_users_list}
                    </th>
                </tr>

                <tr>
                    <th>{$LNG.al_num}</th>
                    <th>{$LNG.al_member}</th>
                    <th>{$LNG.al_message}</th>
                    <th>{$LNG.al_position}</th>
                    <th>{$LNG.al_points}</th>
                    <th>{$LNG.al_coords}</th>
                    <th>{$LNG.al_member_since}</th>
                    <th>{$LNG.al_estate}</th>
                    <th>{$LNG.al_actions}</th>
                </tr>
            </thead>

            <tbody>
                {foreach $memberList as $userID => $memberListRow}
                    <tr>
                        <td>{$memberListRow@iteration}</td>

                        <td>
                            <a href="#" onclick="return Dialog.Playercard({$userID},'{$memberListRow.username|escape:'javascript'}');">
                                {$memberListRow.username|escape:'html'}
                            </a>

                            {if !empty($memberListRow.class)}
                                {foreach $memberListRow.class as $class}
                                    {if !$class@first}&nbsp;{/if}
                                    <span class="galaxy-short-{$class} galaxy-short">
                                        {$ShortStatus.$class}
                                    </span>
                                {/foreach}
                            {/if}
                        </td>

                        <td class="text-center">
                            <a href="#" onclick="return Dialog.PM({$userID});">
                                <img src="{$dpath}img/m.gif"
                                     alt="{$LNG.write_message}"
                                     title="{$LNG.write_message}">
                            </a>
                        </td>

                        <td>
                            {if $memberListRow.rankID == -1}
                                {$founder}
                            {elseif !empty($rankSelectList)}
                                {html_options
                                    class="rankSelect"
                                    name="rank[{$userID}]"
                                    options=$rankSelectList
                                    selected=$memberListRow.rankID}
                            {else}
                                {$rankList[$memberListRow.rankID]}
                            {/if}
                        </td>

                        <td>
                            <span title="{$memberListRow.points|number}">
                                {shortly_number($memberListRow.points)}
                            </span>
                        </td>

                        <td>
                            <a href="game.php?page=galaxy&amp;galaxy={$memberListRow.galaxy}&amp;system={$memberListRow.system}">
                                [{$memberListRow.galaxy}:{$memberListRow.system}:{$memberListRow.planet}]
                            </a>
                        </td>

                        <td>
                            {$memberListRow.register_time}
                        </td>

                        <td>
                            {if $rights.ONLINESTATE}
                                {if $memberListRow.onlinetime < 4}
                                    <span style="color:#00ff66">
                                        {$LNG.al_memberlist_on}
                                    </span>
                                {elseif $memberListRow.onlinetime >= 4 && $memberListRow.onlinetime <= 15}
                                    <span style="color:#ffff66">
                                        {$memberListRow.onlinetime} {$LNG.al_memberlist_min}
                                    </span>
                                {else}
                                    <span style="color:#ff4d4d">
                                        {$LNG.al_memberlist_off}
                                    </span>
                                {/if}
                            {else}
                                -
                            {/if}
                        </td>

                        <td class="text-center">
                            {if $memberListRow.rankID != -1}
                                {if $canKick}
                                    <a href="game.php?page=alliance&amp;mode=admin&amp;action=membersKick&amp;id={$userID}"
                                       onclick="return confirm('{$memberListRow.kickQuestion|escape:'javascript'}');"
                                       title="{$LNG.al_actions}"
                                       style="display:inline-block; vertical-align:middle;">
                                        <img src="{$dpath}pic/abort.gif" alt="">
                                    </a>
                                {/if}
                            {else}
                                -
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>

            <tfoot>
                <tr>
                    <th colspan="9" class="text-center">
                        <a href="game.php?page=alliance&amp;mode=admin">
                            {$LNG.al_back}
                        </a>
                    </th>
                </tr>
            </tfoot>

        </table>

    </div>

</form>

<script src="scripts/base/jquery.tablesorter.js"></script>

<script>
$(function() {

    $.tablesorter.addParser({
        id: 'nopoint',
        is: function(s) {
            return false;
        },
        format: function(s) {
            var lNumber = parseFloat(s.replace(/\./g, ''));
            return isNaN(lNumber) ? 0 : lNumber;
        },
        type: 'numeric'
    });

    $("#memberList").tablesorter({
        headers: {
            0: { sorter: false },
            2: { sorter: false },
            3: { sorter: false },
            4: { sorter: "nopoint" },
            8: { sorter: false }
        },
        debug: false
    });

    $('.rankSelect').on('change', function() {
        $.post(
            'game.php?page=alliance&mode=admin&action=rank&ajax=1',
            $(this).serialize(),
            function(data) {
                NotifyBox(data);
            },
            'json'
        );
    });

});
</script>

{/block}
