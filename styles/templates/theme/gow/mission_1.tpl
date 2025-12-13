{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value=null}
{assign var=next value="m2"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <div class="tut-title">{$LNG.tut_m1_name}</div>

    <div class="tut-text">{$LNG.tut_m1_desc}</div>

    <div class="tut-section-title">{$LNG.tut_objects}</div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m1_quest} {$Si_m1_1}{$No_m1_1}</li>
        <li>{$LNG.tut_m1_quest2} {$Si_m1_2}{$No_m1_2}</li>
        <li>{$LNG.tut_m1_quest3} {$Si_m1_3}{$No_m1_3}</li>
    </ul>

    <div class="tut-reward">
   

    {if $reward_metal > 0}
        {$LNG.tech.901}: <span class="res-metal">{$reward_metal|number}</span><br>
    {/if}

    {if $reward_crystal > 0}
        {$LNG.tech.902}: <span class="res-crystal">{$reward_crystal|number}</span><br>
    {/if}


   {if $reward_deuterium > 0}
        {$LNG.tech.903}: <span class="res-deuterium">{$reward_deuterium|number}</span><br>
    {/if}
   

    {if $reward_darkmatter > 0}
        {$LNG.tech.921}: <span class="res-dm">{$reward_darkmatter|number}</span><br>
    {/if}
</div>

    {if $missionReady}
        <form method="POST">
            <input type="hidden" name="tut_token" value="{$tut_token}">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m2}
            </button>
        </form>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode=m2">{$LNG.fl_continue} →</a>
    </div>

</div>
</div>

{/block}
