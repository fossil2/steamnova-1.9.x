
{block name="title" prepend}{$LNG.lm_alliance}{/block}

{block name="content"}

<form action="game.php?page=alliance&amp;mode=admin&amp;action=transfer" method="post">

    <table class="table table-dark table-sm table-gow fs-12 my-2">
        <tr>
            <th colspan="3" class="text-center">
                {$LNG.al_transfer_alliance}
            </th>
        </tr>

        <tr>
            <td style="width: 30%;">
                {$LNG.al_transfer_to}:
            </td>

            <td style="width: 45%;">
                <select
                    name="newleader"
                    class="form-select form-select-sm"
                    style="background-color: #0d1014; color: #e5efff; border: 1px solid #00345c; width: 100%;"
                    required
                >
                    {html_options options=$transferUserList}
                </select>
            </td>

            <td class="text-center" style="width: 25%;">
                <input
                    type="submit"
                    value="{$LNG.al_transfer_submit}"
                    class="button-blue"
                >
            </td>
        </tr>

        <tr>
            <th colspan="3" class="text-center">
                <a href="game.php?page=alliance&amp;mode=admin">
                    {$LNG.al_back}
                </a>
            </th>
        </tr>
    </table>

</form>

{/block}
