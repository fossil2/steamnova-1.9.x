{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m5"}
{assign var=next value="m7"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">
        {$LNG.tut_m6_name}
    </div>

    <!-- Statusanzeige -->
    <div class="tut-status" style="text-align:center; font-size:16px; margin-bottom:10px;">
        {$livello6}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">
        {$LNG.tut_m6_desc}
    </div>

    <!-- Bild -->
    <div class="tut-img-wrapper" style="text-align:center; margin:10px 0;">
        <a href="game.php?page=defense">
            <img src="{$dpath}gebaeude/407.gif" class="tut-img" style="width:110px; height:auto;">
        </a>
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">{$LNG.tut_objects}</div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m6_quest}  {$Si_m6_1}{$No_m6_1}</li>
        <li>{$LNG.tut_m6_quest2} {$Si_m6_2}{$No_m6_2}</li>
        <li>{$LNG.tut_m6_quest3} {$Si_m6_3}{$No_m6_3}</li>
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

    <!-- Abschlussbutton -->
    {if $missionReady}
        <form method="POST">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m7}
            </button>
        </form>
    {else}
        <div class="tut-hint">
            {$LNG.tut_not_ready}
        </div>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">

        <!-- ZURÜCK -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$prev}">
            ← {$LNG.tut_m5}
        </a>

        <!-- WEITER -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$next}">
            {$LNG.tut_m7} →
        </a>

    </div>

</div>
</div>

{/block}
