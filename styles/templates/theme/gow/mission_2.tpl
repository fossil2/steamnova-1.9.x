{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m1"}
{assign var=next value="m3"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">{$LNG.tut_m2_name}</div>

    <!-- Statusanzeige -->
    <div class="tut-status" style="text-align:center; font-size:16px; margin-bottom:10px;">
        {$livello2}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">
        {$LNG.tut_m2_desc}
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">{$LNG.tut_objects}</div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m2_quest}  {$Si_m2_1}{$No_m2_1}</li>
        <li>{$LNG.tut_m2_quest2} {$Si_m2_2}{$No_m2_2}</li>
        <li>{$LNG.tut_m2_quest3} {$Si_m2_3}{$No_m2_3}</li>
        <li>{$LNG.tut_m2_quest4} {$Si_m2_4}{$No_m2_4}</li>
    </ul>

  <div class="tut-reward">
   
    {if $reward_metal > 0}
        {$LNG.tech.901}: <span class="res-metal">{$reward_metal}</span><br>
    {/if}

    {if $reward_crystal > 0}
        {$LNG.tech.902}: <span class="res-crystal">{$reward_crystal}</span><br>
    {/if}

   {if $reward_deuterium > 0}
        {$LNG.tech.903}: <span class="res-deuterium">{$reward_deuterium}</span><br>
    {/if}
   
    {if $reward_darkmatter > 0}
        {$LNG.tech.921}: <span class="res-dm">{$reward_darkmatter}</span><br>
    {/if}
</div>
    <!-- Mission abschließen -->
    {if $missionReady}
        <form method="POST">
            <input type="hidden" name="tut_token" value="{$tut_token}">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m3}
            </button>
        </form>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">

        <!-- Zurück -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$prev}">
            ← {$LNG.tut_m1}
        </a>

        <!-- Weiter -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$next}">
            {$LNG.tut_m3} →
        </a>

    </div>

</div>
</div>

{/block}
