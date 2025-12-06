{$count = count($productionTable.usedResource)}

<div style="height: 100%; background-color: #2f343a; padding-bottom: 200px;">
  <div style="text-align: center;">
    <table class="table table-dark table-gow table-sm fs-12 w-100 mx-auto">
      <tbody>
        <tr>
          <td colspan="2" style="text-align: center;">
            <table style="margin: 0 auto; width: 80%; font-size: 16px;">
              <tr>
                <th>{$LNG.in_level}</th>
                {if $count > 1}
                  {foreach $productionTable.usedResource as $resourceID}
                  <th colspan="2">{$LNG.tech.$resourceID}</th>
                  {/foreach}
                {/if}
              </tr>
              <tr>
                <th>&nbsp;</th>
                {foreach $productionTable.usedResource as $resourceID}
                <th>{$LNG.in_storage}</th>
                <th>{$LNG.in_difference}</th>
                {/foreach}
              </tr>
              {foreach $productionTable.storage as $elementLevel => $productionData}
              <tr>
                <td><span{if $CurrentLevel == $elementLevel} style="color:#ff0000"{/if}>{$elementLevel}</span></td>
                {foreach $productionData as $resourceID => $storage}
                {$storageDiff = $storage - $productionTable.storage.$CurrentLevel.$resourceID}
                <td><span style="color:{if $storage > 0}lime{elseif $storage < 0}red{else}white{/if}">{$storage|number}</span></td>
                <td><span style="color:{if $storageDiff > 0}lime{elseif $storageDiff < 0}red{else}white{/if}">{$storageDiff|number}</span></td>
                {/foreach}
              </tr>
              {/foreach}
            </table>
          </td>
        </tr>
      </tbody>
    </table>
  </div>
</div>