{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m8"}
{assign var=next value=null}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">
        {$LNG.tut_m9_name} – {$livello9}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">
        {$LNG.tut_m9_desc}
    </div>

    <!-- Bild -->
    <div class="tut-img-wrapper">
        <a href="game.php?page=shipyard">
            <img src="{$dpath}gebaeude/209.gif" class="tut-img">
        </a>
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">
        {$LNG.tut_objects}
    </div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m9_quest}  {$Si_m9_1}{$No_m9_1}</li>
        <li>{$LNG.tut_m9_quest2} {$Si_m9_2}{$No_m9_2}</li>
        <li>{$LNG.tut_m9_quest3} {$Si_m9_3}{$No_m9_3}</li>
        <li>{$LNG.tut_m9_quest4} {$Si_m9_4}{$No_m9_4}</li>
    </ul>

    <!-- Belohnung -->
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

    <!-- Abschluss -->
    {if $missionReady}
        <form method="POST">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_compleat}
            </button>
        </form>
    {else}
        <div class="tut-hint">
            {$LNG.tut_not_ready}
        </div>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">

        <!-- Zurück -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$prev}">
            ← {$LNG.tut_m8}
        </a>

        <!-- Letzte Mission: Button deaktiviert -->
        <span class="tut-nav-btn disabled">Ende ✓</span>

    </div>

</div>
</div>

{/block}
