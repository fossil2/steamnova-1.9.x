
{block name="title" prepend}{$LNG.lm_fleet}{/block}
{block name="content"}

<table class="table table-dark table-sm table-gow fs-12 my-2">

    <thead>
        <tr>
            <th colspan="2"
                class="text-center"
                style="background: #10263b; color: #7fff93; padding: 8px;">
                {$LNG.fl_fleet_sended}
            </th>
        </tr>
    </thead>

    <tbody>
        <tr>
            <td>{$LNG.fl_mission}</td>
            <td>{$LNG["type_mission_{$targetMission}"]}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_distance}</td>
            <td>{$distance|number}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_fleet_speed}</td>
            <td>{$MaxFleetSpeed|number}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_fuel_consumption}</td>
            <td>{$consumption|number}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_from}</td>
            <td>{$from}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_destiny}</td>
            <td>{$destination}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_arrival_time}</td>
            <td>{$fleetStartTime}</td>
        </tr>

        <tr>
            <td>{$LNG.fl_return_time}</td>
            <td>{$fleetEndTime}</td>
        </tr>

        <tr>
            <th colspan="2"
                class="text-center"
                style="background: #10263b; color: #ffd600; padding: 6px;">
                {$LNG.fl_fleet}
            </th>
        </tr>

        {foreach $FleetList as $ShipID => $ShipCount}
        <tr>
            <td>{$LNG.tech.{$ShipID}}</td>
            <td>{$ShipCount|number}</td>
        </tr>
        {/foreach}

    </tbody>

</table>

{/block}
