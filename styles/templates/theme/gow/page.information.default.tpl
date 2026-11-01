
{block name="title" prepend}{$LNG.lm_info}{/block}
{block name="content"}

<table class="table table-dark table-gow table-sm fs-12"
       style="width: 100%; margin: 0; border-collapse: collapse;">

    <tbody>
        <tr>
            <th style="
                background: #10263b;
                color: #ffd600;
                padding: 8px;
                text-align: left;
                border: 1px solid #00345c;
            ">
                {$LNG.tech.$elementID}
            </th>
        </tr>

        <tr>
            <td style="
                background: #101014;
                border: 1px solid #00345c;
                padding: 10px;
            ">

                <div style="
                    color: #d8d8d8;
                    font-size: 12px;
                    line-height: 1.6;
                    text-align: left;
                ">

                    <p style="color: #d8d8d8; margin: 0 0 10px;">
                        {$LNG.longDescription.$elementID}
                    </p>

                    {if !empty($Bonus)}
                        <p>
                            <b style="color: #ffd600;">{$LNG.in_bonus}</b>
                            <br>

                            {foreach $Bonus as $BonusName => $elementBouns}
                                {if $elementBouns[0] < 0}-{else}+{/if}
                                {if $elementBouns[1] == 0}
                                    {abs($elementBouns[0] * 100)}%
                                {else}
                                    {floatval($elementBouns[0])}
                                {/if}

                                {$LNG.bonus.$BonusName}
                                <br>
                            {/foreach}
                        </p>
                    {/if}

                    {if !empty($FleetInfo)}

                        {if !empty($FleetInfo.rapidfire.to)}
                            <p>
                                {foreach $FleetInfo.rapidfire.to as $rapidfireID => $shoots}
                                    {$LNG.in_rf_again}
                                    {$LNG.tech.$rapidfireID}:
                                    <span style="color: #00ff66;">
                                        {$shoots|number}
                                    </span>
                                    <br>
                                {/foreach}
                            </p>
                        {/if}

                        {if !empty($FleetInfo.rapidfire.from)}
                            <p>
                                {foreach $FleetInfo.rapidfire.from as $rapidfireID => $shoots}
                                    {$LNG.in_rf_from}
                                    {$LNG.tech.$rapidfireID}:
                                    <span style="color: #ff4d4d;">
                                        {$shoots|number}
                                    </span>
                                    <br>
                                {/foreach}
                            </p>
                        {/if}

                    {/if}

                </div>

            </td>
        </tr>
    </tbody>
</table>

{if !empty($FleetInfo)}
    {include file="shared.information.shipInfo.tpl"}
{/if}

{if !empty($gateData)}
    {include file="shared.information.gate.tpl"}
{/if}

{if !empty($MissileList)}
    {include file="shared.information.missiles.tpl"}
{/if}

{if !empty($productionTable.production)}
    {include file="shared.information.production.tpl"}
{/if}

{if !empty($productionTable.storage)}
    {include file="shared.information.storage.tpl"}
{/if}

{/block}
