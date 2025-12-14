{block name="content"}

<form id="collectMines"
      class="bg-black w-75 text-white p-3 my-3 mx-auto fs-12"
      action="?page=collectMines&mode=saveSettings"
      method="post">

  <!-- Allow collect under attack -->
  <div class="form-gorup d-flex flex-column my-1 p-2">
    <label class="text-start my-1 cursor-pointer hover-underline"
           for="collect_mines_under_attack">
        Let user collect mines while under attack
    </label>
    <input id="collect_mines_under_attack"
           type="checkbox"
           name="collect_mines_under_attack"
           {if $collect_mines_under_attack}checked="checked"{/if}>
  </div>

  <!-- Cooldown time -->
  <div class="form-gorup d-flex flex-column my-1 p-2">
    <label class="text-start my-1 cursor-pointer hover-underline"
           for="collect_mine_time_minutes">
        Collect Mine Time (minutes)
    </label>
    <input id="collect_mine_time_minutes"
           class="form-control py-1 bg-dark text-white my-1 border border-secondary"
           name="collect_mine_time_minutes"
           value="{$collect_mine_time_minutes}"
           type="number"
           min="1"
           maxlength="5">
  </div>

  <!-- DM cost -->
  <div class="form-gorup d-flex flex-column my-1 p-2">
    <label class="text-start my-1 cursor-pointer hover-underline"
           for="collect_mine_dm_cost">
        Dark Matter cost for resource protection
    </label>
    <input id="collect_mine_dm_cost"
           class="form-control py-1 bg-dark text-white my-1 border border-secondary"
           name="collect_mine_dm_cost"
           value="{$collect_mine_dm_cost}"
           type="number"
           min="0"
           maxlength="5">
    <small class="text-secondary">
        0 = free, value applies to all players
    </small>
  </div>

  <!-- Save -->
  <div class="form-gorup d-flex flex-column my-1 p-2">
    <input class="btn btn-primary text-white"
           value="{$LNG.se_save_parameters}"
           type="submit">
  </div>

</form>

{/block}
