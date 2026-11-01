
{block name="title" prepend}{$LNG.lm_alliance}{/block}

{block name="content"}

<form action="game.php?page=alliance&amp;mode=admin&amp;action=changeTag" method="post">

    <table class="table table-dark table-sm table-gow fs-12 my-2">
        <tr>
            <th colspan="2" class="text-center">
                {$LNG.al_tag} {$LNG.al_change_title}
            </th>
        </tr>

        <tr>
            <td>
                {$LNG.al_new_tag}
            </td>

            <td>
                <input
                    type="text"
                    name="newname"
                    class="form-control form-control-sm d-inline-block"
                    style="width: 65%;"
                    required
                >

                <input
                    type="submit"
                    value="{$LNG.al_change_submit}"
                    class="button-blue"
                >
            </td>
        </tr>

        <tr>
            <th colspan="2" class="text-center">
                <a href="game.php?page=alliance&amp;mode=admin">
                    {$LNG.al_back}
                </a>
            </th>
        </tr>
    </table>

</form>

{/block}
