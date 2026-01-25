{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m6"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">{$LNG.tut_m7_name}</div>

    <!-- Status -->
    <div class="tut-status" style="text-align:center;">
        {$livello7}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">{$LNG.tut_m7_desc}</div>

    <!-- Bild -->
    <div class="tut-img-wrapper" style="text-align:center;">
        <a href="game.php?page=shipyard">
            <img src="{$dpath}gebaeude/210.gif" class="tut-img">
        </a>
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">{$LNG.tut_objects}</div>
    <ul class="tut-task-list">
        <li>{$LNG.tut_m7_quest}  {$Si_m7_1}{$No_m7_1}</li>
        <li>{$LNG.tut_m7_quest3} {$Si_m7_2}{$No_m7_2}</li>
        <li>{$LNG.tut_m7_quest2} {$Si_m7_3}{$No_m7_3}</li>
    </ul>

    <!-- Belohnung -->
    <div class="tut-reward">
        {if $reward_darkmatter > 0}
            {$LNG.tech.921}: <span class="res-dm">{$reward_darkmatter}</span>
        {/if}
    </div>

    <!-- ABSCHLIESSEN (nur wenn bereit, aber noch nicht abgeschlossen) -->
{if $missionReady}
<form method="POST" action="">
    <input type="hidden" name="tut_token" value="{$tut_token}">
    <button class="tut-button-finish" type="submit" name="complete" value="1">
        {$LNG.tut_go_to} {$LNG.tut_m8}
    </button>
</form>
{/if}

    <!-- Hinweis -->
    {if !$missionReady && !$missionFinished}
        <div class="tut-hint">{$LNG.tut_not_ready}</div>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">

        <!-- ZURÜCK -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode=m6">
            ← {$LNG.tut_m6}
        </a>

        <!-- WEITER (nur wenn abgeschlossen) -->
      <a class="tut-nav-btn" href="game.php?page=tutorial&mode=m8">
        {$LNG.tut_m8} →
    </a>
    </div>

</div>
</div>

{/block}
