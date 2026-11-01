
<form action="?page=fleetTable&amp;action=acs" method="post" class="acs-form">
    <input name="fleetID" value="{$acsData.mainFleetID}" type="hidden">

    <div class="acs-panel">

        <div class="acs-header">
            {$LNG.fl_sac_of_fleet}
        </div>

        <div class="acs-name-header">
            {$LNG.fl_modify_sac_name}
            (<a href="javascript:Rename();">{$LNG.fl_acs_change}</a>)
        </div>

        <div class="acs-name" id="acsName">
            {$acsData.acsName}
        </div>

        {if !empty($acsData.statusMessage)}
            <div class="acs-status">
                {$acsData.statusMessage}
            </div>
        {/if}

        <div class="acs-content">

            <div class="acs-column">
                <div class="acs-label">
                    {$LNG.fl_members_invited}
                </div>

                <select size="5" class="acs-select">
                    {html_options options=$acsData.invitedUsers}
                </select>
            </div>

            <div class="acs-column">
                <div class="acs-label">
                    {$LNG.fl_invite_members}
                </div>

                <input
                    name="username"
                    type="text"
                    class="acs-input"
                    autocomplete="off"
                >

                <input
                    type="submit"
                    class="acs-button"
                    value="{$LNG.fl_continue}"
                >
            </div>

        </div>
    </div>
</form>

<script type="text/javascript">
function Rename() {
    var Name = prompt("{$LNG.fl_acs_change_name}", "{$acsData.acsName}");

    if (Name === null || Name.trim() === "") {
        return;
    }

    $.getJSON(
        '?page=fleetTable&action=acs',
        {
            fleetID: '{$acsData.mainFleetID}',
            acsName: Name
        },
        function(data) {
            if (data != "") {
                alert(data);
                return;
            }

            $('#acsName').text(Name);
        }
    );
}
</script>
