{block name="title" prepend}
{$LNG.lm_technology}{/block}

{block name="content"}

    <style>
    
.tech-nav {
    display: flex;
    flex-wrap: wrap;
    justify-content: space-between;
    gap: 1rem;
    margin: 1rem;
}


		.tech-nav-list {
			display: flex;
			align-items: center;
			gap: 0.3rem;
			padding: 0.5rem;
			cursor: pointer;
		}

		.tech-nav-list:hover, .tech-nav-list.active {
			background-color: #555454ab;
			border: 1px solid rgb(74, 74, 78);
			border-radius: 5px;
		}

		.tech-nav-text {
			font-size: larger;
			font-weight: 100;
		}

		.tech-list {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			gap: 1rem;
		}

		.box-container {
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            gap: 1rem;
            max-width: 225px;
            min-width: 225px;
            border: 1px solid rgb(74, 74, 78);
            border-radius: 5px;
            padding: 1rem;
            box-shadow: 0 0px 10px 2px rgba( 31, 38, 135, 0.37 );
            backdrop-filter: blur( 0.5px );
        }

		.tech-text {
			font-size: larger;
			text-align: center;
			font-weight: 100;
		}

		.tech-img img {
			width: 100px;
			height: 100px;
			cursor: pointer;
		}

		.tech-requis {
			display: flex;
			flex-wrap: wrap;
			justify-content: center;
			gap: 1rem;
			max-width: 254px;
		}

		.tech-requis-info {
			position: relative;
			overflow: hidden;
		}

		.tech-requis-info img {
			width: 50px;
			height: 50px;
			cursor: pointer;
		}

		.tech-requis-level {
			position: absolute;
			width: 100%;
			bottom: 0;
			right: 0;
			text-align: end;
			background-color: #555454ab;
			color: white;
			font-weight: 600;
			font-size: 0.7rem;
			padding: 0.2rem;
		}
	</style>

    <script>
		$(document).ready(function() {
			$('.tech-nav-list').each(function() {
				const techNavList = $(this);
				
				techNavList.click(function() {
					$('.tech-nav-list').each(function() {
						$(this).removeClass('active');
					});
					techNavList.addClass('active');

					const techDataClass = techNavList.data('tech-class');
					$('.tech-list').each(function() {
						const boxContainer = $(this);
						const techDataContent = boxContainer.data('tech-content-class');
						if (techDataClass === techDataContent) {
							boxContainer.show();
						} else {
							boxContainer.hide();
						}
					});
				});
			});
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.gif" alt=""
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.gif" alt=""
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.gif" alt=""
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.gif" alt=""
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.gif" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.gif" alt=""
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
                                <img onclick="return Dialog.info({$elementID})" src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$elementID}.jpg" alt="">
                            </div>
                            {if $requireList}
                                <div class="tech-requis">
                                    {foreach $requireList as $requireID => $NeedLevel}
                                        <div class="tech-requis-info" onclick="return Dialog.info({$requireID})">
                                            <img src="https://mkmatz.eno-intern.de/styles/theme/gow/gebaeude/{$requireID}.jpg" alt=""
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