{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m7"}
{assign var=next value="m9"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">
        {$LNG.tut_m8_name}
    </div>

    <!-- Statusanzeige -->
    <div class="tut-status" style="text-align:center; font-size:16px; margin-bottom:10px;">
        {$livello8}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">
        {$LNG.tut_m8_desc}
    </div>

    <!-- Bild -->
    <div class="tut-img-wrapper" style="text-align:center; margin: 10px 0;">
        <a href="game.php?page=shipyard">
            <img src="{$dpath}gebaeude/208.gif" class="tut-img" style="width:110px; height:auto;">
        </a>
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">
        {$LNG.tut_objects}
    </div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m8_quest}  {$Si_m8_1}{$No_m8_1}</li>
        <li>{$LNG.tut_m8_quest2} {$Si_m8_2}{$No_m8_2}</li>
        <li>{$LNG.tut_m8_quest3} {$Si_m8_3}{$No_m8_3}</li>
    </ul>

    <!-- Belohnung -->
    <div class="tut-reward">
        {$LNG.tut_m8_gain}
    </div>

    <!-- Mission abschließen -->
    {if $missionReady}
        <form method="POST">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m9}
            </button>
        </form>
    {else}
        <div class="tut-hint">
            {$LNG.tut_not_ready}
        </div>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">

        <!-- Vorherige Mission -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$prev}">
            ← {$LNG.tut_m7}
        </a>

        <!-- Nächste Mission -->
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$next}">
            {$LNG.tut_m9} →
        </a>

    </div>

</div>
</div>

{/block}
