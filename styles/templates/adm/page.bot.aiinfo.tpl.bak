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

                <th>Bau</th>
                <th>Nächste Aktion</th>
            </tr>
        </thead>

        <tbody>
        {foreach $bots as $bot}
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
                <td>
                    {$bot.energy - $bot.energy_used}
                </td>

                <td>{$bot.metal_store}</td>
                <td>{$bot.crystal_store}</td>
                <td>{$bot.deuterium_store}</td>

                <td>{$bot.robot_factory}</td>
                <td>{$bot.hangar}</td>
                <td>{$bot.laboratory}</td>

                <td>{$bot.build_status}</td>
                <td>{$bot.next_action}</td>
            </tr>
        {/foreach}
        </tbody>
    </table>

</div>
{/block}
