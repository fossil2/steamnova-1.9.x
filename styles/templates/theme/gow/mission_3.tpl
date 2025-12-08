{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

{assign var=prev value="m2"}
{assign var=next value="m4"}

<div class="tut-glass-wrapper">
<div class="tut-glass-card">

    <!-- Titel -->
    <div class="tut-title">{$LNG.tut_m3_name}</div>

    <!-- Statusanzeige -->
    <div class="tut-status" style="text-align:center; font-size:16px; margin-bottom:10px;">
        {$livello3}
    </div>

    <!-- Beschreibung -->
    <div class="tut-text">
        {$LNG.tut_m3_desc}
    </div>

    <!-- Bild -->
    <div style="text-align:center; margin: 10px 0;">
        <a href="game.php?page=buildings">
            <img src="{$dpath}gebaeude/4.gif" style="width:110px; height:auto;">
        </a>
    </div>

    <!-- Aufgaben -->
    <div class="tut-section-title">{$LNG.tut_objects}</div>

    <ul class="tut-task-list">
        <li>{$LNG.tut_m3_quest}  {$Si_m3_1}{$No_m3_1}</li>
        <li>{$LNG.tut_m3_quest2} {$Si_m3_2}{$No_m3_2}</li>
        <li>{$LNG.tut_m3_quest3} {$Si_m3_3}{$No_m3_3}</li>
    </ul>

    <!-- Belohnung -->
    <div class="tut-reward">{$LNG.tut_m3_gain}</div>

    <!-- Abschlussbutton -->
    {if $missionReady}
        <form method="POST">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m4}
            </button>
        </form>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$prev}">← {$LNG.tut_m2}</a>
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode={$next}">{$LNG.tut_m4} →</a>
    </div>

</div>
</div>

{/block}
