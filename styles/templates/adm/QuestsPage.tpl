{block name="content"}

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.2.1/css/all.min.css" />
<link rel="stylesheet" type="text/css" href="./styles/resource/css/admin/quest.css">
<script type="text/javascript" src="./scripts/admin/quest.js"></script>

<center>

<div id="panneau">

    <ul id="onglets" class="clearfix">
        <li class="actif_tab">Information</li>
        <li>Listen</li>
        <li>{$LNG.quest_11}</li>
    </ul>

    <div id="contenus">

        {* =========================================================
           INFORMATION
           ========================================================= *}

        <div class="onglet_content">

            <h1>{$LNG.adm_quest_info_title}</h1>

            {$LNG.adm_quest_info_content}

        </div>


        {* =========================================================
           LISTEN
           ========================================================= *}

        <div class="onglet_content">

            <h1>{$LNG.quest_17}</h1>

            <div>

                <table>

                    <tr class="categories_tab">

                        <th class="first">
                            Kategorie ID
                        </th>

                        <th>
                            Name
                        </th>

                        <th class="end">
                            Aktionen
                        </th>

                    </tr>


                    {foreach from=$list_categories|@json_decode item=categorie}

                        <tr
                            id="cat_{$categorie->id}"
                            class="cat{$categorie->categorieID}"
                        >

                            <td>
                                {$categorie->categorieID}
                            </td>

                            <td>
                                {$categorie->name}
                            </td>

                            <td>

                                <a
                                    style="cursor:pointer;"
                                    onclick="javascript:modalCategorie({$categorie->id})"
                                    title="Bearbeiten"
                                >
                                    <i
                                        class="fa-solid fa-pen"
                                        style="color:#237e23; padding-right:20px;"
                                    ></i>
                                </a>


                                <a
                                    onclick="javascript:deleteCategorie({$categorie->id})"
                                    style="cursor:pointer;"
                                    title="Löschen"
                                >
                                    <i
                                        class="fa-solid fa-trash"
                                        style="color:#c23934;"
                                    ></i>
                                </a>

                            </td>

                        </tr>

                    {/foreach}

                </table>

            </div>


            <hr>


            <h1>{$LNG.quest_18}</h1>


            <div>

                <table>

                    <tr>

                        <th class="first">
                            {$LNG.quest_19}
                        </th>

                        <th>
                            {$LNG.quest_23}
                        </th>

                        <th>
                            {$LNG.nws_title}
                        </th>

                        <th>
                            {$LNG.quest_24}
                        </th>

                        <th>
                            {$LNG.quest_25}
                        </th>

                        <th>
                            Points {$LNG.quest_20}
                        </th>

                        <th>
                            {$LNG.tech.901} {$LNG.quest_20}
                        </th>

                        <th>
                            {$LNG.tech.902} {$LNG.quest_20}
                        </th>

                        <th>
                            {$LNG.tech.903} {$LNG.quest_20}
                        </th>

                        <th>
                            {$LNG.tech.921} {$LNG.quest_20}
                        </th>

                        <th>
                            {$LNG.quest_26}
                        </th>

                        <th>
                            {$LNG.quest_27}
                        </th>

                        <th>
                            {$LNG.quest_28}
                        </th>

                        <th>
                            {$LNG.quest_29}
                        </th>

                        <th class="end">
                            {$LNG.quest_30}
                        </th>

                    </tr>


                    {foreach from=$result_list_quests|@json_decode item=quete}

                        <tr
                            id="quest_{$quete->questsID}"
                            class="quest{$quete->questsID}"
                        >

                            <td>
                                {$quete->questsID}
                            </td>

                            <td>
                                {$LNG["quest_categorie_{$quete->questsCategories}"]}
                            </td>

                            <td>
                                {$quete->quest_title}
                            </td>

                            <td>
                                {$quete->quest_description}
                            </td>

                            <td>
                                +{$quete->quest_objectif_level|number}
                                x
                                {$LNG.tech.{$quete->quest_objectif}}
                            </td>

                            <td>
                                {$quete->quest_points_reward|number}
                            </td>

                            <td>
                                {$quete->quest_metal_reward|number}
                            </td>

                            <td>
                                {$quete->quest_crystal_reward|number}
                            </td>

                            <td>
                                {$quete->quest_deuterium_reward|number}
                            </td>

                            <td>
                                {$quete->quest_darkmatter_reward|number}
                            </td>

                            <td>

                                {if $quete->quest_actif}

                                    <span
                                        data-before-text="{$LNG.gl_yes}"
                                        data-before-type="green pill"
                                    ></span>

                                {else}

                                    <span
                                        data-before-text="{$LNG.gl_no}"
                                        data-before-type="red pill"
                                    ></span>

                                {/if}

                            </td>

                            <td>

                                <em>
                                    {$quete->quest_created}
                                </em>

                            </td>

                            <td>

                                {if $quete->quest_event}

                                    <span
                                        data-before-text="{$LNG.gl_yes}"
                                        data-before-type="green pill"
                                    ></span>

                                {else}

                                    <span
                                        data-before-text="{$LNG.gl_no}"
                                        data-before-type="red pill"
                                    ></span>

                                {/if}

                            </td>

                            <td>

                                {if $quete->quest_time_finish_event == "0"}

                                    -

                                {else if $quete->quest_time_finish_event == "-1"}

                                    {$LNG.quest_event_finished}

                                {else}

                                    {$quete->quest_time_finish_event}

                                {/if}

                            </td>

                            <td>

                                <a
                                    style="cursor:pointer;"
                                    onclick="javascript:modalQuest({$quete->questsID})"
                                    title="Bearbeiten"
                                >
                                    <i
                                        class="fa-solid fa-pen"
                                        style="color:#237e23; padding-right:20px;"
                                    ></i>
                                </a>


                                <a
                                    onclick="javascript:deleteQuest({$quete->questsID})"
                                    style="cursor:pointer;"
                                    title="Löschen"
                                >
                                    <i
                                        class="fa-solid fa-trash"
                                        style="color:#c23934;"
                                    ></i>
                                </a>

                            </td>

                        </tr>

                    {/foreach}

                </table>

            </div>

        </div>


        {* =========================================================
           QUEST / KATEGORIE ERSTELLEN
           ========================================================= *}

        <div class="onglet_content">


            {* -----------------------------------------------------
               Kategorie erstellen
               ----------------------------------------------------- *}

            <h1>
                {$LNG.quest_01}
            </h1>


            <form id="addCategorie" method="post">

                <input
                    type="hidden"
                    name="action"
                    value="add_categorie"
                >


                <div class="form-control">

                    <label>
                        Kategorie ID
                    </label>

                    <select name="categorie_add">
    <option value="">Kategorie auswählen</option>

    <option value="1">
        1 - {$LNG.quest_categorie_1}
    </option>

    <option value="2">
        2 - {$LNG.quest_categorie_2}
    </option>

    <option value="3">
        3 - {$LNG.quest_categorie_3}
    </option>

    <option value="4">
        4 - {$LNG.quest_categorie_4}
    </option>

    <option value="5">
        5 - {$LNG.quest_categorie_5}
    </option>
</select>

                </div>


                <button type="submit">
                    {$LNG.quest_12}
                </button>

            </form>


            <hr>


            {* -----------------------------------------------------
               Quest erstellen
               ----------------------------------------------------- *}

            <h1>
                {$LNG.quest_02}
            </h1>


            <form id="addQuest" method="post">


                <input
                    type="hidden"
                    name="action"
                    value="add_quest"
                >


                {* ---------------------------------------------
                   Aktiv
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="actif_quete">
                        {$LNG.quest_03}
                    </label>

                    <input
                        id="actif_quete"
                        name="quest_actif"
                        type="checkbox"
                        style="width:auto;"
                    >

                </div>


                {* ---------------------------------------------
                   Kategorie
                   --------------------------------------------- *}

                <div class="form-control">

                    <label>
                        {$LNG.quest_04}
                    </label>


                    <select name="categories_id">

                        <option value="">
                            {$LNG.quest_04}
                        </option>


                        {foreach from=$list_categories|@json_decode item=categorie}

                            <option value="{$categorie->categorieID}">
                                {$categorie->name}
                            </option>

                        {/foreach}

                    </select>

                </div>


                {* ---------------------------------------------
                   Aufgabe / Ziel
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="quest_obj_create">
                        {$LNG.quest_select_task}
                    </label>


                    <select
                        name="quest_obj"
                        id="quest_obj_create"
                    >

                        <option value="">
                            {$LNG.quest_select_task}
                        </option>


                        {foreach from=$objectifs_list|@json_decode key=key item=objectif}

                            <option
                                value="{$key}"
                                data-type="{$objectif->type}"
                                data-name="{$objectif->name|escape:'html'}"
                            >
                                {$objectif->name}
                            </option>

                        {/foreach}

                    </select>

                </div>


                {* ---------------------------------------------
                   Anzahl / Stufe
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="quest_obj_level_create">
                        {$LNG.quest_08}
                    </label>

                    <input
                        id="quest_obj_level_create"
                        type="number"
                        name="quest_obj_level"
                        value="1"
                        min="1"
                    >

                </div>


                {* Diese beiden Felder bleiben vorerst bestehen,
                   solange addQuest() noch angepasst wird. *}

                <input
                    type="hidden"
                    name="quest_title"
                    id="quest_title_create"
                    value="auto"
                >

                <input
                    type="hidden"
                    name="quest_desc"
                    id="quest_desc_create"
                    value="auto"
                >


                {* ---------------------------------------------
                   Belohnungen
                   --------------------------------------------- *}

                <div class="form-control">

                    <label>
                        {$LNG.quest_09}
                    </label>

                    <input
                        type="number"
                        name="quest_redward_points"
                        value="0"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.901}
                    </label>

                    <input
                        type="number"
                        name="quest_redward_metal"
                        value="0"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.902}
                    </label>

                    <input
                        type="number"
                        name="quest_redward_crystal"
                        value="0"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.903}
                    </label>

                    <input
                        type="number"
                        name="quest_redward_deuterium"
                        value="0"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.921}
                    </label>

                    <input
                        type="number"
                        name="quest_redward_darkmatter"
                        value="0"
                        min="0"
                    >

                </div>


                {* ---------------------------------------------
                   Event
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="actif_quete_event">
                        {$LNG.quest_event}
                    </label>

                    <input
                        id="actif_quete_event"
                        name="quest_event"
                        type="checkbox"
                        style="width:auto;"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_14}
                    </label>

                    <input
                        type="datetime-local"
                        name="quest_time_finish_event"
                        pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                    >

                </div>


                <button type="submit">
                    {$LNG.quest_15}
                </button>


            </form>

        </div>

    </div>

</div>

</center>



{* =============================================================
   KATEGORIE BEARBEITEN
   ============================================================= *}

<div class="modal_popup">

    <div class="bg_shadow"></div>

    <div class="popup">

        <div class="popup_head">

            <span id="test_cat"></span>

            <button
                class="close_btn"
                type="button"
                onclick="javascript:modalCategorieClose()"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <form
            class="popup_form"
            id="editCategorie"
            method="post"
        >

            <div class="popup_body">

                <input
                    type="hidden"
                    name="action"
                    value="edit_categorie"
                >

                <input
                    type="hidden"
                    name="id"
                    id="hidden_cat"
                >


                <label>
                    {$LNG.quest_36}
                </label>

                <br>


                <input
                    type="number"
                    name="catId"
                    id="form_cat_id"
                >

            </div>


            <div class="popup_foot">

                <button
                    class="popup_confirm_btn"
                    type="submit"
                >
                    Speichern
                </button>

            </div>

        </form>

    </div>

</div>



{* =============================================================
   QUEST BEARBEITEN
   ============================================================= *}

<div class="modal_popup_quest">

    <div class="bg_shadow"></div>

    <div class="popup">

        <div class="popup_head">

            <span id="title_quest"></span>

            <button
                class="close_btn"
                type="button"
                onclick="javascript:modalQuestClose()"
            >
                <i class="fa-solid fa-xmark"></i>
            </button>

        </div>


        <form
            class="popup_form"
            id="editQuest"
            method="post"
        >

            <input
                type="hidden"
                name="id"
                id="hidden_quest"
            >


            <div class="popup_body">


                <input
                    type="hidden"
                    name="action"
                    value="edit_quest"
                >


                {* ---------------------------------------------
                   Aktiv
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="actif_quete_modal">
                        {$LNG.quest_03}
                    </label>

                    <input
                        id="actif_quete_modal"
                        name="quest_actif"
                        type="checkbox"
                        style="width:auto;"
                    >

                </div>


                {* ---------------------------------------------
                   Kategorie
                   --------------------------------------------- *}

                <div class="form-control">

                    <label>
                        {$LNG.quest_04}
                    </label>


                    <select
                        id="quest_categorie_modal"
                        name="categories_id"
                    >

                        <option value="">
                            {$LNG.quest_04}
                        </option>


                        {foreach from=$list_categories|@json_decode item=categorie}

                            <option value="{$categorie->categorieID}">
                                {$categorie->name}
                            </option>

                        {/foreach}

                    </select>

                </div>


                {* ---------------------------------------------
                   Aufgabe / Ziel
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="quest_obj_modal">
                        {$LNG.quest_select_task}
                    </label>


                    <select
                        id="quest_obj_modal"
                        name="quest_obj"
                    >

                        <option value="">
                            {$LNG.quest_select_task}
                        </option>


                        {foreach from=$objectifs_list|@json_decode key=key item=objectif}

                            <option
                                value="{$key}"
                                data-type="{$objectif->type}"
                                data-name="{$objectif->name|escape:'html'}"
                            >
                                {$objectif->name}
                            </option>

                        {/foreach}

                    </select>

                </div>


                {* ---------------------------------------------
                   Anzahl / Stufe
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="quest_obj_level">
                        {$LNG.quest_08}
                    </label>

                    <input
                        id="quest_obj_level"
                        type="number"
                        name="quest_obj_level"
                        min="1"
                    >

                </div>


                {* Alte Felder bleiben unsichtbar vorhanden,
                   damit quest.js vorerst kompatibel bleibt. *}

                <input
                    id="quest_title"
                    type="hidden"
                    name="quest_title"
                >

                <textarea
                    id="quest_description"
                    name="quest_desc"
                    style="display:none;"
                ></textarea>


                {* ---------------------------------------------
                   Belohnungen
                   --------------------------------------------- *}

                <div class="form-control">

                    <label>
                        {$LNG.quest_09}
                    </label>

                    <input
                        id="quest_reward_point"
                        type="number"
                        name="quest_redward_points"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.901}
                    </label>

                    <input
                        id="quest_reward_metal"
                        type="number"
                        name="quest_redward_metal"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.902}
                    </label>

                    <input
                        id="quest_reward_crystal"
                        type="number"
                        name="quest_redward_crystal"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.903}
                    </label>

                    <input
                        id="quest_reward_deuterium"
                        type="number"
                        name="quest_redward_deuterium"
                        min="0"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_10} {$LNG.tech.921}
                    </label>

                    <input
                        id="quest_reward_darkmatter"
                        type="number"
                        name="quest_redward_darkmatter"
                        min="0"
                    >

                </div>


                {* ---------------------------------------------
                   Event
                   --------------------------------------------- *}

                <div class="form-control">

                    <label for="actif_quete_event_modal">
                        {$LNG.quest_event}
                    </label>

                    <input
                        id="actif_quete_event_modal"
                        name="quest_event"
                        type="checkbox"
                        style="width:auto;"
                    >

                </div>


                <div class="form-control">

                    <label>
                        {$LNG.quest_14}
                    </label>

                    <input
                        id="quest_event_time"
                        type="datetime-local"
                        name="quest_time_finish_event"
                        pattern="[0-9]{4}-[0-9]{2}-[0-9]{2}T[0-9]{2}:[0-9]{2}"
                    >

                </div>

            </div>


            <div class="popup_foot">

                <button
                    class="popup_confirm_btn"
                    type="submit"
                >
                    {$LNG.quest_15}
                </button>

            </div>

        </form>

    </div>

</div>

{/block}