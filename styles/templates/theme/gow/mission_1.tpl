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

    <div class="tut-reward">{$LNG.tut_m1_gain}</div>

    {if $missionReady}
        <form method="POST">
            <button class="tut-button-finish" name="complete">
                {$LNG.tut_go_to} {$LNG.tut_m2}
            </button>
        </form>
    {/if}

    <!-- Navigation -->
    <div class="tut-nav-wrapper">
        <a class="tut-nav-btn disabled">← Zurück</a>
        <a class="tut-nav-btn" href="game.php?page=tutorial&mode=m2">Weiter →</a>
    </div>

</div>
</div>

{/block}
