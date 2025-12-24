{block name="title" prepend}
  {if $mode == "defense"}{$LNG.lm_defenses}{else}{$LNG.lm_shipshard}{/if}
{/block}

{block name="content"}

<script src="./scripts/base/avoid_submit_on_refresh.js" type="text/javascript"></script>

<script>
function showItem(id){
  if ($('#item_big_' + id).hasClass('d-none')) {
    $('.buildItemBig').addClass('d-none');
    $('.buildItemSmall').removeClass('border-color-active').removeClass('border-color-passive');
    $('#item_big_' + id).removeClass('d-none');
    $('#item_small_' + id).addClass('border-color-active');
  } else {
    $('#item_big_' + id).addClass('d-none');
    $('#item_small_' + id).addClass('border-color-passive').removeClass('border-color-active');
  }
}
</script>

{if !$NotBuilding}
<span class="d-flex justify-content-center rounded p-2 text-danger fw-bold bg-dark border border-2 border-danger mx-auto my-2 w-100">
  {$LNG.bd_building_shipyard}
</span>
{/if}

<div class="ItemsWrapper">

<div
{if $mode == "defense"}
style="background:url('{$dpath}images/defense.webp') no-repeat center center; background-size:cover;"
{else}
style="background:url('{$dpath}images/hangar.webp') no-repeat center center; background-size:cover;"
{/if}
class="itemShow d-flex justify-content-center align-items-center w-100 bg-black position-relative border-gray">

{foreach $elementList as $ID => $Element}

<div id="item_big_{$ID}" class="buildItemBig position-absolute top-0 left-0 d-none flex-column d-flex rounded border border-1 border-dark w-100">

  <div class="d-flex w-100 itemTop">
    <div class="bg-black d-flex align-items-start justify-content-center">
      <img class="mx-2 hover-pointer"
           onclick="return Dialog.info({$ID})"
           src="{$dpath}gebaeude/{$ID}.gif"
           alt="{$LNG.tech.{$ID}}"
           width="120" height="120">
    </div>

    <div class="d-flex flex-column w-100 bg-light-black">

      <div class="bg-blue d-flex mb-2 text-white fw-bold">
        <div class="px-2">
          <span class="fs-12 {if $Element.costOverflowTotal > 0}color-red{else}color-yellow{/if}">
            {$LNG.tech.{$ID}}
          </span>
          <span class="fs-12 text-white" id="val_{$ID}">
            &nbsp;({$LNG.bd_available} {$Element.available|number})
          </span>
        </div>
      </div>

      <div class="d-flex mx-2 justify-content-between">

        <div>
          {foreach $Element.costResources as $RessID => $RessAmount}
          <div class="d-flex align-items-center my-1">
            <img src="{$dpath}gebaeude/{$RessID}.{if $RessID >=600 && $RessID <=699}jpg{else}gif{/if}"
                 title="{$LNG.tech.$RessID}">
            <span class="mx-1 fs-11 {if empty($Element.costOverflow[$RessID])}text-white{else}color-red{/if}">
              {$RessAmount|number}
            </span>
          </div>
          {/foreach}
        </div>

        <span class="fs-10 my-1 text-white">
          {$LNG.fgf_time}: {pretty_time($Element.elementTime)}
        </span>

        <div class="d-flex flex-column align-items-end">

          {if $ID == 212}
            <span class="fs-12 text-white">
              <span class="color-green">+{$SolarEnergy}</span> {$LNG.tech.911}
            </span>
          {/if}

          {if $Element.AlreadyBuild}
            <span class="fs-12 color-red">{$LNG.bd_protection_shield_only_one}</span>
          {elseif $NotBuilding && $Element.buyable}
            <form action="game.php?page=shipyard&amp;mode={$mode}" method="post">
              <input class="p-1 fs-11 text-white" type="text" name="fmenge[{$ID}]" size="3" maxlength="{$maxlength}" value="0">
              <input class="p-1 fs-11 text-white" type="button"
                     value="{$LNG.bd_max_ships}"
                     onclick="this.form.elements['fmenge[{$ID}]'].value='{$Element.maxBuildable}'">
              <input class="p-1 fs-11 text-white button-upgrade" type="submit" value="{$LNG.bd_build_ships}">
            </form>
          {/if}

        </div>
      </div>
    </div>
  </div>

  <div class="bg-light-black">
    <p class="text-white fs-11 p-2">
      {$LNG.shortDescription[$ID]}
    </p>
  </div>

</div>
{/foreach}

</div>

<div class="d-flex flex-wrap justify-content-center bg-black pb-2 border-gray">

  <div class="w-100 text-center m-2">
    <span class="color-yellow fs-12 fw-bold">
      {if $mode == "fleet"}{$LNG.lm_shipshard}{else}{$LNG.lm_defenses}{/if}
      | {$LNG.st_points}
      [{if $mode == "fleet"}{$userFleetPoints}{else}{$userDefensePoints}{/if}]
    </span>
  </div>

  <div class="mx-2 d-flex flex-wrap justify-content-center">

    {foreach $elementList as $ID => $Element}
    <div class="buildItemSmall position-relative d-flex user-select-none"
         id="item_small_{$ID}"
         onclick="showItem({$ID})"
         title="{$LNG.tech.{$ID}}">

      <div class="position-absolute bottom-0 end-0 color-yellow bg-dark fs-11 ps-1">
        {shortly_number($Element.available)}
      </div>

      {if !$Element.buyable || !$Element.technologySatisfied}
        <div class="black-screen position-absolute top-0 end-0"></div>
      {/if}

      <img src="{$dpath}gebaeude/{$ID}.gif" alt="{$LNG.tech.{$ID}}" width="80" height="80">
    </div>
    {/foreach}

  </div>
</div>

</div>

{if !empty($BuildList)}
<div class="ItemsWrapper bg-black my-2 p-2">

  <form action="game.php?page=shipyard&mode={$mode}" method="post" class="text-center">
    <input type="hidden" name="action" value="delete">
    <select name="auftr[]" multiple class="p-2 color-yellow fs-11">
      <option>&nbsp;</option>
    </select>
    <p class="text-danger fw-bold my-2">{$LNG.bd_cancel_warning}</p>
    <button class="btn btn-secondary text-white fw-bold">{$LNG.bd_cancel_send}</button>
  </form>

</div>
{/if}

{/block}

{block name="script" append}
<script>
data = {$BuildList|json};
bd_operating = '{$LNG.bd_operating}';
bd_available = '{$LNG.bd_available}';
</script>

{if !empty($BuildList)}
<script src="scripts/base/bcmath.js"></script>
<script src="scripts/game/shipyard.js"></script>
<script>
$(function(){ ShipyardInit(); });
</script>
{/if}
{/block}
