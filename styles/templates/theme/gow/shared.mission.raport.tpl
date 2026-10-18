{block name="title" prepend}{$pageTitle}{/block}
{block name="content"}

<div class="kb-wrapper text-center">

    {* ===================================================== *}
    {* TITEL + ZEIT *}
    {* ===================================================== *}

    <div class="kb-title">
        {if $Raport.mode == 1}
            {$LNG.sys_destruc_title}
        {else}
            {$LNG.sys_attack_title}
        {/if}
        <div class="kb-time"><small>{$Raport.time}</small></div>
    </div>

    {* ===================================================== *}
    {* SPIELER *}
    {* ===================================================== *}

    <div class="kb-section kb-players">
        <div class="kb-grid-2">
            <div class="kb-side kb-attacker">
                <div class="kb-label">{$LNG.sys_attack_attacker_pos}</div>
                <div class="kb-names">
                    {if isset($Raport.attackers) && !empty($Raport.attackers)}
                        {foreach $Raport.attackers as $name}
                            <span class="kb-name">{$name}</span>{if !$name@last}<span class="kb-sep">, </span>{/if}
                        {/foreach}
                    {else}
                        -
                    {/if}
                </div>
            </div>

            <div class="kb-side kb-defender">
                <div class="kb-label">{$LNG.sys_attack_defender_pos}</div>
                <div class="kb-names">
                    {if isset($Raport.defenders) && !empty($Raport.defenders)}
                        {foreach $Raport.defenders as $name}
                            <span class="kb-name">{$name}</span>{if !$name@last}<span class="kb-sep">, </span>{/if}
                        {/foreach}
                    {else}
                        -
                    {/if}
                </div>
            </div>
        </div>
    </div>

    {* ===================================================== *}
    {* STARTFLOTTEN *}
    {* ===================================================== *}

    {if isset($Raport.startUnits)}
    <div class="kb-section">
        <h4 class="kb-heading">{$LNG.kb_fleets_stood_against}</h4>

        <table class="kb-table kb-table-compact">
            <tr>
                <td style="width:50%; vertical-align:top;">
                    <div class="kb-col-title kb-attacker">{$LNG.sys_attack_attacker_pos}</div>
                    {foreach $Raport.startUnits.attacker as $fleet}
                        {if isset($fleet.unit) && !empty($fleet.unit)}
                            {foreach $fleet.unit as $id => $count}
                                <div class="kb-line"><span>{$LNG.shortNames.$id}</span><span>{$count|number}</span></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    {/foreach}
                </td>

                <td style="width:50%; vertical-align:top;">
                    <div class="kb-col-title kb-defender">{$LNG.sys_attack_defender_pos}</div>
                    {foreach $Raport.startUnits.defender as $fleet}
                        {if isset($fleet.unit) && !empty($fleet.unit)}
                            {foreach $fleet.unit as $id => $count}
                                <div class="kb-line"><span>{$LNG.shortNames.$id}</span><span>{$count|number}</span></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    {/foreach}
                </td>
            </tr>
        </table>
    </div>
    {/if}

    {* ===================================================== *}
    {* KAMPFRUNDEN *}
    {* ===================================================== *}

    {if isset($Raport.rounds) && !empty($Raport.rounds)}
        {foreach $Raport.rounds as $roundNo => $round}
        <div class="kb-section kb-round-box">

            <h4 class="kb-heading">{$LNG.kb_round} {$roundNo}</h4>

            <table class="kb-table kb-table-compact">
                <tr>
                    <td style="width:50%; vertical-align:top;">
                        <div class="kb-col-title kb-attacker">{$LNG.kb_attacker_before_round}</div>
                        {if isset($round.attacker_before) && !empty($round.attacker_before)}
                            {foreach $round.attacker_before as $fleetId => $units}
                                {if !empty($units)}
                                    {foreach $units as $unitId => $count}
                                        <div class="kb-line"><span>{$LNG.shortNames.$unitId}</span><span>{$count|number}</span></div>
                                    {/foreach}
                                {else}
                                    <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                                {/if}
                                <div class="kb-gap"></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    </td>

                    <td style="width:50%; vertical-align:top;">
                        <div class="kb-col-title kb-defender">{$LNG.kb_defender_before_round}</div>
                        {if isset($round.defender_before) && !empty($round.defender_before)}
                            {foreach $round.defender_before as $fleetId => $units}
                                {if !empty($units)}
                                    {foreach $units as $unitId => $count}
                                        <div class="kb-line"><span>{$LNG.shortNames.$unitId}</span><span>{$count|number}</span></div>
                                    {/foreach}
                                {else}
                                    <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                                {/if}
                                <div class="kb-gap"></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    </td>
                </tr>

                <tr>
                    <td style="width:50%; vertical-align:top;">
                        <div class="kb-col-title kb-attacker">{$LNG.kb_attacker_after_round}</div>
                        {if isset($round.attacker_after) && !empty($round.attacker_after)}
                            {foreach $round.attacker_after as $fleetId => $units}
                                {if !empty($units)}
                                    {foreach $units as $unitId => $count}
                                        <div class="kb-line"><span>{$LNG.shortNames.$unitId}</span><span>{$count|number}</span></div>
                                    {/foreach}
                                {else}
                                    <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                                {/if}
                                <div class="kb-gap"></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    </td>

                    <td style="width:50%; vertical-align:top;">
                        <div class="kb-col-title kb-defender">{$LNG.kb_defender_after_round}</div>
                        {if isset($round.defender_after) && !empty($round.defender_after)}
                            {foreach $round.defender_after as $fleetId => $units}
                                {if !empty($units)}
                                    {foreach $units as $unitId => $count}
                                        <div class="kb-line"><span>{$LNG.shortNames.$unitId}</span><span>{$count|number}</span></div>
                                    {/foreach}
                                {else}
                                    <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                                {/if}
                                <div class="kb-gap"></div>
                            {/foreach}
                        {else}
                            <div class="kb-destroyed">{$LNG.sys_destroyed}</div>
                        {/if}
                    </td>
                </tr>
            </table>

        </div>
        {/foreach}
    {/if}

    {* ===================================================== *}
    {* SIEGER + VERLUSTE *}
    {* ===================================================== *}

    <div class="kb-section kb-summary">

        <div class="kb-result">
            {if $Raport.result == "a"}
                <span class="kb-win">{$LNG.sys_attacker_won}</span>
            {elseif $Raport.result == "r"}
                <span class="kb-lose">{$LNG.sys_defender_won}</span>
            {else}
                <span class="kb-draw">{$LNG.sys_both_won}</span>
            {/if}
        </div>

        <div class="kb-stats">
            <div class="kb-stat-row">
                <span>{$LNG.sys_attacker_lostunits}</span>
                <strong>{$Raport.units[0]|number} {$LNG.sys_units}</strong>
            </div>
            <div class="kb-stat-row">
                <span>{$LNG.sys_defender_lostunits}</span>
                <strong>{$Raport.units[1]|number} {$LNG.sys_units}</strong>
            </div>
        </div>

        <div class="kb-debris">
            {$LNG.debree_field_1}
            {foreach $Raport.debris as $elementID => $amount}
                {$amount|number} {$LNG.tech.$elementID}{if !$amount@last}, {/if}
            {/foreach}
            {$LNG.debree_field_2}
        </div>

       {if isset($Raport.moon) && $Raport.moon.moonChance > 0}
    <div class="kb-moon">
        <b>{$LNG.kb_moon_chance}:</b> {$Raport.moon.moonChance}%
    </div>

    {if isset($Raport.moon.moonCreated) && $Raport.moon.moonCreated}
        <div class="kb-moon-created">
            <div class="kb-moon-created-text">
                {$LNG.kb_moon_created}
            </div>

            <video class="kb-moon-video" autoplay muted loop controls playsinline>
                <source src="/media/Storyboard5.mp4" type="video/mp4">
                Dein Browser unterstützt kein HTML5-Video.
            </video>
        </div>
    {/if}
{/if}
</div>

    {* ===================================================== *}
    {* REBUILD *}
    {* ===================================================== *}

    {if isset($Raport.rebuild) && !empty($Raport.rebuild)}
    <div class="kb-section kb-rebuild">
        <h4 class="kb-heading">{$LNG.kb_repaired_defense}</h4>

        {foreach $Raport.rebuild as $id => $amount}
            <div class="kb-line"><span>+ {$LNG.shortNames.$id}</span><span>{$amount|number}</span></div>
        {/foreach}
    </div>
    {/if}

    {* ===================================================== *}
    {* ZUSATZINFO *}
    {* ===================================================== *}

    {if isset($Raport.additionalInfo) && $Raport.additionalInfo != ""}
    <div class="kb-section kb-extra">
        {$Raport.additionalInfo}
    </div>
    {/if}

</div>

<style>
.kb-wrapper {
    max-width: 980px;
    margin: 0 auto;
    color: #d7dde8;
    font-size: 13px;
    line-height: 1.35;
}

.kb-title {
    margin-bottom: 12px;
    padding: 10px 12px;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    background: rgba(255,255,255,0.03);
    font-size: 20px;
    font-weight: 700;
}

.kb-time {
    margin-top: 4px;
    font-size: 12px;
    opacity: 0.8;
}

.kb-section {
    margin-bottom: 10px;
    padding: 10px 12px;
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 10px;
    background: rgba(255,255,255,0.025);
    text-align: left;
}

.kb-heading {
    margin: 0 0 8px 0;
    font-size: 14px;
    font-weight: 700;
}

.kb-grid-2 {
    display: table;
    width: 100%;
    table-layout: fixed;
}

.kb-grid-2 > div {
    display: table-cell;
    width: 50%;
    vertical-align: top;
}

.kb-side {
    padding: 0 6px;
}

.kb-label {
    font-size: 12px;
    font-weight: 700;
    margin-bottom: 4px;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    opacity: 0.9;
}

.kb-attacker {
    color: #7fd1ff;
}

.kb-defender {
    color: #ff9c9c;
}

.kb-names {
    color: #f1f5fb;
    font-weight: 600;
}

.kb-name {
    white-space: nowrap;
}

.kb-table {
    width: 100%;
    border-collapse: collapse;
}

.kb-table td {
    padding: 8px 10px;
    border: 1px solid rgba(255,255,255,0.06);
    vertical-align: top;
}

.kb-table-compact td {
    padding: 8px 10px;
}

.kb-col-title {
    margin-bottom: 6px;
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.35px;
}

.kb-line {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 1px 0;
    color: #e8edf7;
}

.kb-gap {
    height: 4px;
}

.kb-destroyed {
    color: #ff8d8d;
    font-style: italic;
    padding: 2px 0;
}

.kb-round-box {
    padding-top: 8px;
}

.kb-summary {
    text-align: center;
}

.kb-result {
    margin-bottom: 8px;
    font-size: 16px;
    font-weight: 700;
}

.kb-win {
    color: #7dff9d;
}

.kb-lose {
    color: #ff9090;
}

.kb-draw {
    color: #f3f5f7;
}

.kb-stats {
    max-width: 520px;
    margin: 0 auto 8px auto;
    text-align: left;
}

.kb-stat-row {
    display: flex;
    justify-content: space-between;
    gap: 12px;
    padding: 2px 0;
}

.kb-debris,
.kb-moon,
.kb-extra {
    margin-top: 8px;
}

.kb-rebuild .kb-line {
    max-width: 360px;
}

@media (max-width: 700px) {
    .kb-grid-2,
    .kb-grid-2 > div {
        display: block;
        width: 100%;
    }

    .kb-side {
        padding: 0;
        margin-bottom: 10px;
    }

    .kb-table td {
        display: block;
        width: 100% !important;
    }

    .kb-stat-row {
        display: block;
    }
}

.kb-moon-created {
    margin-top: 12px;
    padding: 12px;
    border: 1px solid rgba(127, 209, 255, 0.25);
    border-radius: 10px;
    background: rgba(127, 209, 255, 0.06);
    text-align: center;
}

.kb-moon-created-text {
    margin-bottom: 10px;
    color: #7fd1ff;
    font-weight: 700;
    font-size: 14px;
    text-shadow: 0 0 8px rgba(127, 209, 255, 0.35);
}

.kb-moon-video {
    width: 100%;
    max-width: 360px;
    border-radius: 8px;
    box-shadow: 0 0 20px rgba(127, 209, 255, 0.12);
}


</style>

{/block}