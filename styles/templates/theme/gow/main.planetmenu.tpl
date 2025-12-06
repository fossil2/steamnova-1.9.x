{if $AllPlanets}
  <div class="row justify-content-center my-1;" style="gap: 1%;">
  {foreach $AllPlanets as $PlanetRow}
    <div class="col-5 border border-0 {if isset($PlanetRow.moonInfo)}
    {if $PlanetRow.moonInfo[0].selected || $PlanetRow.selected}
    border-danger
    {else}
    border-secondary
    {/if}
  {else}
    {if $PlanetRow.selected}
    border-danger
    {else}
    border-secondary
    {/if}
  {/if}" style="padding: 10px;">
      <div class="d-flex justify-content-between">
        <a class="d-flex hover-pointer text-decoration-none" href="game.php?page={$page}&amp;{if !empty($mode)}mode={$mode}&amp;{/if}cp={$PlanetRow.id}" data-bs-toggle="tooltip"
          data-bs-placement="left"
          data-bs-html="true"
          title="
          <table class='table-tooltip fs-11'>
            <thead>
              <tr>
                <th class='text-start color-yellow' colspan='2'>{$PlanetRow.name}&nbsp;[{$PlanetRow.galaxy}:{$PlanetRow.system}:{$PlanetRow.planet}]</th>
              </tr>
            </thead>
            <tbody>
              <tr>
                <td class='text-start color-blue fw-bold'>{$LNG.pm_fields}:</td>
                <td class='text-end'>({$PlanetRow.field_current}&nbsp;/&nbsp;{$PlanetRow.field_max})</td>
              </tr>
              <tr>
                <td class='text-start color-blue fw-bold'>{$LNG.pm_diameter}:</td>
                <td class='text-end'>{$PlanetRow.diameter}</td>
              </tr>
              <tr>
                <td class='text-start color-blue fw-bold'>{$LNG.pm_min_temperature}:</td>
                <td class='text-end'>{$PlanetRow.temp_min}</td>
              </tr>
              <tr>
                <td class='text-start color-blue fw-bold'>{$LNG.pm_max_temperature}:</td>
                <td class='text-end'>{$PlanetRow.temp_max}</td>
              </tr>
            </tbody>
          </table>
          ">
            <div class="d-flex" style="gap: 5px;flex-direction: column;">
              <div>
               <img class="mx-2 rounded-circle {if $PlanetRow.selected}pl_highlight{else}pl_hover{/if}" src="{$dpath}planeten/{$PlanetRow.image}.png" width="50" height="50" alt="{$PlanetRow.name}">
              </div>
              <div class="d-flex flex-column text-yellow align-items-center justify-content-start fs-11">
                  <span>{$PlanetRow.name}</span>
                  <span>[{$PlanetRow.galaxy}:{$PlanetRow.system}:{$PlanetRow.planet}]</span>
              </div>
            </div>
        </a>
        {if isset($PlanetRow.moonInfo)}
          <div>
            <a class="hover-pointer" href="game.php?page={$page}&amp;cp={$PlanetRow.moonInfo[0].id}"
              data-bs-toggle="tooltip"
              data-bs-placement="left"
              data-bs-html="true"
              title="
              <table class='table-tooltip fs-11'>
                <thead>
                  <tr>
                    <th class='text-start color-yellow' colspan='2'>{$PlanetRow.moonInfo[0].name}&nbsp;[{$PlanetRow.moonInfo[0].galaxy}:{$PlanetRow.moonInfo[0].system}:{$PlanetRow.moonInfo[0].planet}]</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td class='text-start color-blue fw-bold'>{$LNG.pm_fields}:</td>
                    <td class='text-end'>({$PlanetRow.moonInfo[0].field_current}&nbsp;/&nbsp;{$PlanetRow.moonInfo[0].field_max})</td>
                  </tr>
                  <tr>
                    <td class='text-start color-blue fw-bold'>{$LNG.pm_diameter}:</td>
                    <td class='text-end'>{$PlanetRow.moonInfo[0].diameter}</td>
                  </tr>
                  <tr>
                    <td class='text-start color-blue fw-bold'>{$LNG.pm_min_temperature}:</td>
                    <td class='text-end'>{$PlanetRow.moonInfo[0].temp_min}</td>
                  </tr>
                  <tr>
                    <td class='text-start color-blue fw-bold'>{$LNG.pm_max_temperature}:</td>
                    <td class='text-end'>{$PlanetRow.moonInfo[0].temp_max}</td>
                  </tr>
                </tbody>
              </table>
            ">
              <img class="mx-2 rounded-circle {if $PlanetRow.moonInfo[0].selected}pl_highlight{else}pl_hover{/if}" src="{$dpath}planeten/{$PlanetRow.moonInfo[0].image}.jpg" width="25" height="25" alt="{$PlanetRow.moonInfo[0].name}">
            </a>
          </div>
        {/if}
      </div>
    </div>
  {/foreach}
  </div>
{/if}
