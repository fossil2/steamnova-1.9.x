{block name="content"}
<div class="bg-black w-95 text-white p-3 my-3 mx-auto fs-12">

    <h2 class="text-yellow mb-3">🤖 AI-Bot Übersicht</h2>

    <table class="table table-dark table-sm table-bordered text-center">
        <thead>
            <tr class="text-yellow">
                <th>Bot</th>
                <th>Planet</th>
                <th>Koords</th>

                <th>Metal</th>
                <th>Kristall</th>
                <th>Deut</th>

                <th>Metal-Mine</th>
                <th>Kristall-Mine</th>
                <th>Deut-Synth</th>

                <th>Solar</th>
                <th>Energie</th>

                <th>M-Speicher</th>
                <th>K-Speicher</th>
                <th>D-Speicher</th>

                <th>Robot</th>
                <th>Werft</th>
                <th>Labor</th>


                <th>Raketen</th>
                <th>K-Laser</th>
                <th>S-Laser</th>
                <th>Gauss</th>
                <th>Ion</th>
                <th>Plasma</th>
                <th>K-Schild</th>
                <th>G-Schild</th>

                <th>Defense-Score</th>

                <th>Bau</th>
                <th>Nächste Aktion</th>
            </tr>
        </thead>

        <tbody>
        {foreach $bots as $bot}

            {* ==== Defense Defaults ==== *}
            {assign var=ml value=$bot.misil_launcher|default:0}
            {assign var=sl value=$bot.small_laser|default:0}
            {assign var=bl value=$bot.big_laser|default:0}
            {assign var=ga value=$bot.gauss_canyon|default:0}
            {assign var=io value=$bot.ionic_canyon|default:0}
            {assign var=pl value=$bot.buster_canyon|default:0}
            {assign var=ss value=$bot.small_protection_shield|default:0}
            {assign var=bs value=$bot.big_protection_shield|default:0}

            {* ==== Defense Score ==== *}
            {assign var=defscore value=
                $ml*1 +
                $sl*2 +
                $bl*3 +
                $io*4 +
                $ga*6 +
                $pl*10 +
                $ss*20 +
                $bs*40
            }

            {* ==== 🧪 Research Mapping ==== *}
            {assign var=techName value=$bot.tech_name|default:'-'}

            <tr>
                <td>{$bot.username}</td>
                <td>{$bot.planet_name}</td>
                <td>[{$bot.galaxy}:{$bot.system}:{$bot.planet}]</td>

                <td>{$bot.metal|number_format}</td>
                <td>{$bot.crystal|number_format}</td>
                <td>{$bot.deuterium|number_format}</td>

                <td>{$bot.metal_mine}</td>
                <td>{$bot.crystal_mine}</td>
                <td>{$bot.deuterium_sintetizer}</td>

                <td>{$bot.solar_plant}</td>
                <td>{$bot.energy - $bot.energy_used}</td>

                <td>{$bot.metal_store}</td>
                <td>{$bot.crystal_store}</td>
                <td>{$bot.deuterium_store}</td>

                <td>{$bot.robot_factory}</td>
                <td>{$bot.hangar}</td>
                <td>{$bot.laboratory}</td>

               

                {* ==== Defense Values ==== *}
                <td>{$ml}</td>
                <td>{$sl}</td>
                <td>{$bl}</td>
                <td>{$ga}</td>
                <td>{$io}</td>
                <td>{$pl}</td>
                <td>{$ss}</td>
                <td>{$bs}</td>

                {* ==== Defense Rating ==== *}
                <td>
                    {if $defscore == 0}
                        <span class="text-red">Keine</span>
                    {elseif $defscore < 50}
                        <span class="text-orange">Schwach ({$defscore})</span>
                    {elseif $defscore < 200}
                        <span class="text-yellow">OK ({$defscore})</span>
                    {else}
                        <span class="text-green">Gut ({$defscore})</span>
                    {/if}
                </td>

                <td>{$bot.build_status}</td>
                <td>{$bot.next_action}</td>

            </tr>
        {/foreach}
        </tbody>
    </table>
    
   <h3 class="text-yellow mt-4 mb-2">🔬 Forschungsübersicht</h3>

<table class="table table-dark table-sm table-bordered text-center">

<thead>
<tr class="text-yellow">
    <th>Bot</th>
    <th>Fertig um</th>

    <th>Spio</th>
    <th>Computer</th>
    <th>Militär</th>
    <th>Verteidigung</th>
    <th>Schild</th>

    <th>Energie</th>
    <th>Hyperraum</th>
    <th>Verbrennung</th>
    <th>Impuls</th>
    <th>Hyperantrieb</th>

    <th>Laser</th>
    <th>Ionen</th>
    <th>Plasma</th>

    <th>IG-Netz</th>
    <th>Expedition</th>

    <th>M-Prod</th>
    <th>K-Prod</th>
    <th>D-Prod</th>
</tr>
</thead>

<tbody>

{foreach $bots as $bot}

<tr>

<td>{$bot.username}</td>

{* --- Aktive Forschung --- *}

{if $bot.research_active}
    <td>
        {$bot.research_active.name}
        <br>
        <small>{$bot.research_active.end}</small>
    </td>
{else}
    <td><span class="text-red">-</span></td>
{/if}

{* --- Levelmatrix --- *}

<td>{$bot.research.106.level|default:0}</td>
<td>{$bot.research.108.level|default:0}</td>
<td>{$bot.research.109.level|default:0}</td>
<td>{$bot.research.110.level|default:0}</td>
<td>{$bot.research.111.level|default:0}</td>

<td>{$bot.research.113.level|default:0}</td>
<td>{$bot.research.114.level|default:0}</td>
<td>{$bot.research.115.level|default:0}</td>
<td>{$bot.research.117.level|default:0}</td>
<td>{$bot.research.118.level|default:0}</td>

<td>{$bot.research.120.level|default:0}</td>
<td>{$bot.research.121.level|default:0}</td>
<td>{$bot.research.122.level|default:0}</td>

<td>{$bot.research.123.level|default:0}</td>
<td>{$bot.research.124.level|default:0}</td>

<td>{$bot.research.131.level|default:0}</td>
<td>{$bot.research.132.level|default:0}</td>
<td>{$bot.research.133.level|default:0}</td>

</tr>

{/foreach}

</tbody>
</table> 

</div>
{/block}
