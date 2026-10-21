{block name="title" prepend}
{$LNG.lm_technology}{/block}

{block name="content"}
<style>
.techWrapper {
    width: 100%;
    box-sizing: border-box;

    background: linear-gradient(
        180deg,
        rgba(34, 42, 56, 0.98) 0%,
        rgba(20, 26, 38, 0.98) 52%,
        rgba(11, 16, 25, 1) 100%
    );

    border: 1px solid rgba(58, 118, 178, 0.40);
    border-radius: 14px;

    box-shadow:
        0 10px 26px rgba(0, 0, 0, 0.32),
        0 0 8px rgba(45, 120, 200, 0.13),
        inset 0 1px 0 rgba(255,255,255,0.03);

    padding: 12px;
    margin-top: 8px;
}

.tech-nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: flex-start;
    gap: 10px;

    margin: 0 0 12px 0;
    padding-bottom: 8px;

    border-bottom: 1px solid rgba(70, 130, 190, 0.18);
}

.tech-nav-list {
    display: flex;
    align-items: center;
    gap: 0.4rem;

    padding: 0.55rem 0.8rem;

    cursor: pointer;

    color: #d7e2ef;

    background: linear-gradient(
        180deg,
        rgba(34, 42, 56, 0.98) 0%,
        rgba(20, 26, 38, 0.98) 52%,
        rgba(11, 16, 25, 1) 100%
    );

    border: 1px solid rgba(58, 118, 178, 0.35);
    border-radius: 10px;

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.04),
        0 2px 4px rgba(0,0,0,0.25);

    transition:
        background 0.18s ease,
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        transform 0.18s ease;
}

.tech-nav-list:hover {
    background: linear-gradient(
        180deg,
        rgba(52, 72, 98, 1) 0%,
        rgba(28, 45, 68, 1) 50%,
        rgba(16, 28, 46, 1) 100%
    );

    border-color: rgba(84, 155, 220, 0.75);

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.08),
        0 0 8px rgba(45, 120, 200, 0.22),
        0 2px 6px rgba(0,0,0,0.35);

    transform: translateY(-1px);
}

.tech-nav-list.active {
    background: linear-gradient(
        180deg,
        rgba(28, 78, 125, 1) 0%,
        rgba(16, 53, 92, 1) 52%,
        rgba(10, 33, 62, 1) 100%
    );

    border-color: rgba(88, 170, 235, 0.90);

    box-shadow:
        inset 0 1px 0 rgba(255,255,255,0.10),
        0 0 10px rgba(40, 125, 210, 0.28),
        0 2px 6px rgba(0,0,0,0.35);
}

.tech-nav-list.active .tech-nav-text,
.tech-nav-list.active i {
    color: #ffd600;
}

.tech-nav-text {
    font-size: 15px;
    font-weight: 600;
}

.tech-list {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;

    gap: 12px;

    width: 100%;
    box-sizing: border-box;
}

.box-container {
    display: flex;
    flex-direction: column;
    align-items: center;

    gap: 12px;

    width: 225px;
    min-width: 225px;
    max-width: 225px;

    box-sizing: border-box;

    border: 1px solid rgba(58, 118, 178, 0.40);
    border-radius: 14px;

    padding: 1rem;

    background: linear-gradient(
        180deg,
        rgba(27, 35, 48, 0.98) 0%,
        rgba(17, 23, 34, 0.98) 55%,
        rgba(10, 15, 24, 1) 100%
    );

    box-shadow:
        0 6px 16px rgba(0,0,0,0.28),
        0 0 7px rgba(45,120,200,0.10),
        inset 0 1px 0 rgba(255,255,255,0.03);

    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        transform 0.18s ease;
}

.box-container:hover {
    border-color: rgba(84, 155, 220, 0.62);

    box-shadow:
        0 8px 18px rgba(0,0,0,0.32),
        0 0 8px rgba(45,120,200,0.16),
        inset 0 1px 0 rgba(255,255,255,0.04);

    transform: translateY(-1px);
}

.tech-text {
    font-size: 17px;
    text-align: center;
    font-weight: 600;

    color: #d7e2ef;

    text-shadow:
        0 1px 2px rgba(0,0,0,0.85);
}

.tech-img img {
    width: 100px;
    height: 100px;

    cursor: pointer;

    border-radius: 12px;

    border: 1px solid rgba(58, 118, 178, 0.28);

    box-shadow:
        0 2px 7px rgba(0,0,0,0.30);

    transition:
        border-color 0.18s ease,
        box-shadow 0.18s ease,
        transform 0.18s ease;
}

.tech-img img:hover {
    border-color: rgba(84, 155, 220, 0.70);

    box-shadow:
        0 0 8px rgba(45,120,200,0.18),
        0 3px 8px rgba(0,0,0,0.32);

    transform: translateY(-1px);
}

.tech-requis {
    display: flex;
    flex-wrap: wrap;
    justify-content: center;

    gap: 10px;

    max-width: 100%;
}

.tech-requis-info {
    position: relative;
    overflow: hidden;

    border-radius: 8px;

    border: 1px solid rgba(58, 118, 178, 0.28);

    background: rgba(14, 18, 26, 0.96);
}

.tech-requis-info img {
    width: 50px;
    height: 50px;

    cursor: pointer;
    display: block;
}

.tech-requis-level {
    position: absolute;

    width: 100%;

    bottom: 0;
    right: 0;

    text-align: end;

    background: rgba(11, 16, 25, 0.92);

    color: white;

    font-weight: 700;
    font-size: 0.72rem;

    padding: 0.2rem 0.3rem;
}
</style>

<script>
$(document).ready(function() {
    $('.tech-nav-list').each(function() {
        const techNavList = $(this);

        techNavList.click(function() {
            $('.tech-nav-list').removeClass('active');
            techNavList.addClass('active');

            const techDataClass = String(techNavList.data('tech-class'));

            $('.tech-list').each(function() {
                const techList = $(this);
                const techDataContent = String(techList.data('tech-content-class'));

                if (techDataClass === techDataContent) {
                    techList.css('display', 'flex');
                } else {
                    techList.hide();
                }
            });
        });
    });

    $('.tech-nav-list').first().addClass('active');
    $('.tech-list').hide();
    $('.tech-list[data-tech-content-class="0"]').css('display', 'flex');
});
</script>

    <div class="techWrapper">
        <div class="tech-nav">
            {foreach $TechTreeList as $elementID => $requireList}
                {if !is_array($requireList)}
                    <div class="tech-nav-list" data-tech-class="{$requireList}">
                        <i id="button_icon_{$requireList}" class="bi bi-plus-circle"></i>
                        <span class="tech-nav-text">{$LNG.tech.$requireList}</span>
                    </div>
                {/if}
            {/foreach}
        </div>

        <div>
            <div class="tech-list" data-tech-content-class="0">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID < 100}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.gif" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="tech-list" data-tech-content-class="100">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID > 100 && $elementID < 200}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.gif" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="tech-list" data-tech-content-class="200">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID > 200 && $elementID < 300}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.gif" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="tech-list" data-tech-content-class="400">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID > 400 && $elementID < 500}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.gif" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="tech-list" data-tech-content-class="500">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID > 500 && $elementID < 600}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.gif" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>

            <div class="tech-list" data-tech-content-class="600">
                {foreach $TechTreeList as $elementID => $requireList}
                    {if is_array($requireList) && $elementID > 600 && $elementID < 700}
                        <div class="box-container">
                            <div class="tech-text">
                                {$LNG.tech.{$elementID}}
                            </div>
                            <div class="tech-img">
                                <img onclick="return Dialog.info({$elementID})" src="{$dpath}gebaeude/{$elementID}.jpg" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="{$dpath}gebaeude/{$requireID}.jpg" alt=""
                                                    title="{$LNG.tech.{$requireID}}">
                                            <div class="tech-requis-level" style="color:{if $NeedLevel.own < $NeedLevel.count}red{else}lime{/if};">
                                                {min($NeedLevel.count, $NeedLevel.own)} / {$NeedLevel.count}
                                            </div>
                                        </div>
                                    {/foreach}
                                </div>
                            {/if}
                        </div>
                    {/if}
                {/foreach}
            </div>
        </div>

    </div>
{/block}
