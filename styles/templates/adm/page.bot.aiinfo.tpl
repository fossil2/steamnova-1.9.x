{block name="content"}
<div class="bg-black w-95 text-white p-3 my-3 mx-auto fs-12">

<h2 class="text-yellow mb-3">🤖 AI-Bot Übersicht</h2>

<table class="table table-dark table-sm table-bordered text-center">
<thead>
<tr class="text-yellow">
    <th>Bot</th>
    <th>Planeten</th>
    <th>Ressourcen</th>
    <th>Bauqueue</th>
    <th>Nächste Aktion</th>
</tr>
</thead>

<tbody>
{foreach $bots as $bot}
<tr>

<td class="text-yellow">{$bot.username}</td>

<td class="text-left">
{foreach $bot.planets as $p}
    {$p.name} [{$p.coords}]<br>
{/foreach}
</td>

<td class="text-left">

{foreach $bot.planets as $p}

<div style="border-bottom:1px solid #333; margin-bottom:6px; padding-bottom:4px;">

<b class="text-yellow">{$p.name}</b>
<span style="color:#888">[{$p.coords}]</span><br>

M {$p.metal|number_format}
K {$p.crystal|number_format}
D {$p.deuterium|number_format}<br>

Mine {$p.metal_mine}/{$p.crystal_mine}/{$p.deut_synth}<br>

Solar {$p.solar}
{if $p.energy < 0}
<span class="text-red">({$p.energy})</span>
{else}
<span class="text-green">({$p.energy})</span>
{/if}
<br>

Speicher {$p.metal_store}/{$p.crystal_store}/{$p.deut_store}<br>

Robo {$p.robot} Werft {$p.hangar} Lab {$p.lab}<br>

Defense:
R {$p.ml}
KL {$p.sl}
SL {$p.bl}
G {$p.ga}
I {$p.io}
P {$p.pl}
KS {$p.ss}
GS {$p.bs}

</div>

{/foreach}

</td>

<td class="text-left">
{foreach $bot.planets as $p}
    {if $p.build_end > time()}
        <span class="text-orange">{$p.build_status}</span><br>
    {else}
        <span class="text-green">frei</span><br>
    {/if}
{/foreach}
</td>

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
    <th>Aktiv</th>
    <th>Spio</th>
    <th>Computer</th>
    <th>Militär</th>
    <th>Schild</th>
    <th>Energie</th>
    <th>Hyper</th>
    <th>Verbrennung</th>
    <th>Impuls</th>
    <th>Hyperantrieb</th>
</tr>
</thead>

<tbody>
{foreach $bots as $bot}
<tr>

<td>{$bot.username}</td>

<td>
{if $bot.research_active}
    <span class="text-yellow">{$bot.research_active.name}</span><br>
    <small>{$bot.research_active.end}</small>
{else}
    <span class="text-gray">frei</span>
{/if}
</td>

<td>{$bot.research.106.level|default:0}</td>
<td>{$bot.research.108.level|default:0}</td>
<td>{$bot.research.109.level|default:0}</td>
<td>{$bot.research.111.level|default:0}</td>
<td>{$bot.research.113.level|default:0}</td>
<td>{$bot.research.114.level|default:0}</td>
<td>{$bot.research.115.level|default:0}</td>
<td>{$bot.research.117.level|default:0}</td>
<td>{$bot.research.118.level|default:0}</td>

</tr>
{/foreach}
</tbody>
</table>

</div>
{/block}