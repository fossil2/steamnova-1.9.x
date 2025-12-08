{block name="title" prepend}{$LNG.tut_welcome}{/block}
{block name="content"}

<link rel="stylesheet" href="styles/theme/gow/tutorial_glass.css" />

<div class="tut-glass-wrapper">
<div class="tut-glass-card" style="max-width: 750px;">

    <!-- Titel -->
    <div class="tut-title" style="font-size:28px;">
        {$LNG.tut_welcome}
    </div>

    <!-- Intro-Text -->
    <div class="tut-text" style="font-size:17px; text-align:center;">
        {$LNG.tut_welcom_desc}
    </div>

    <div class="tut-section-title" style="margin-top:25px;">
        {$LNG.tut_objects}
    </div>

    <!-- Feature-Liste -->
    <ul class="tut-task-list">
        <li>{$LNG.tut_welcom_desc2}</li>
        <li>{$LNG.tut_welcom_desc3}</li>
        <li>{$LNG.tut_welcom_desc4}</li>
        <li>{$LNG.tut_welcom_desc5}</li>
    </ul>

    <!-- Start-Button -->
    <div style="text-align:center; margin-top:30px;">
        <a href="game.php?page=tutorial&mode=m1">
            <button class="tut-button-finish" style="width:260px; font-size:20px;">
                🚀 {$LNG.tut_go}
            </button>
        </a>
    </div>

</div>
</div>

{/block}
