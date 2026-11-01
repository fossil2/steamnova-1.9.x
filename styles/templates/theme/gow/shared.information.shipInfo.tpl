
{* SteamNova - Schiffs- und Verteidigungsinformationen *}

<div style="
    background: #101014;
    border: 1px solid #00345c;
    box-shadow: 0 0 10px rgba(0,75,135,0.65);
    padding: 10px;
    color: #d8d8d8;
    box-sizing: border-box;
    width: 100%;
">

    <div style="width: 100%; overflow-x: auto;">

        <table class="table table-dark table-sm fs-12"
               style="width: 100%; margin: 0; border-collapse: collapse;">

            <tbody>

                {if !empty($FleetInfo.tech)}
                <tr>
                    <td style="width:50%; color:#8ecfff;">
                        {$LNG.in_engine}
                    </td>
                    <td style="width:50%; color:#ffd600;">
                        {if $FleetInfo.tech == 1}
                            {$LNG.tech.115}
                        {elseif $FleetInfo.tech == 2}
                            {$LNG.tech.117}
                        {elseif $FleetInfo.tech == 3}
                            {$LNG.tech.118}
                        {elseif $FleetInfo.tech == 4}
                            {$LNG.tech.115}
                            <span style="color:#8ecfff;">({$LNG.tech.117})</span>
                        {else}
                            -
                        {/if}
                    </td>
                </tr>
                {/if}

                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_struct_pt}</td>
                    <td style="color:#ffd600;">{$FleetInfo.structure|number}</td>
                </tr>

                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_attack_pt}</td>
                    <td style="color:#ffd600;">{$FleetInfo.attack|number}</td>
                </tr>

                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_shield_pt}</td>
                    <td style="color:#ffd600;">{$FleetInfo.shield|number}</td>
                </tr>

                {if !empty($FleetInfo.capacity)}
                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_capacity}</td>
                    <td style="color:#ffd600;">{$FleetInfo.capacity|number}</td>
                </tr>
                {/if}

                {if !empty($FleetInfo.speed1)}
                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_base_speed}</td>
                    <td style="color:#ffd600;">
                        {$FleetInfo.speed1|number}
                        {if $FleetInfo.speed1 != $FleetInfo.speed2}
                            <span style="color:#8ecfff;">
                                ({$FleetInfo.speed2|number})
                            </span>
                        {/if}
                    </td>
                </tr>
                {/if}

                {if !empty($FleetInfo.consumption1)}
                <tr>
                    <td style="color:#8ecfff;">{$LNG.in_consumption}</td>
                    <td style="color:#ffd600;">
                        {$FleetInfo.consumption1|number}
                        {if $FleetInfo.consumption1 != $FleetInfo.consumption2}
                            <span style="color:#8ecfff;">
                                ({$FleetInfo.consumption2|number})
                            </span>
                        {/if}
                    </td>
                </tr>
                {/if}

            </tbody>
        </table>

    </div>
</div>
