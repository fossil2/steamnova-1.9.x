<div class="fleetWrapper d-flex flex-column">
  <div class="position-relative w-100 d-flex align-items-center">
    {if empty($fleets)}
      <span class="text-center color-blue fs-11 fw-bold w-100">{$LNG.fm_no_fleet_movements}</span>
    {else}
      <span class="fs-14 color-gray text-center">{$LNG.fm_fleets}</span>
    {/if}
    {if !empty($fleets)}
    <button style="height:18px;width:18px;" onclick="showHideFleets();" class="btn btn-sm d-flex align-items-center justify-content-center p-0 m-0 position-absolute hover-pointer end-0 top-0" type="button" name="button">
      <i style="color:#ddd;" class="bi bi-caret-down-square-fill"></i>
    </button>
    {/if}
  </div>

    {foreach $fleets as $index => $fleet}
    <div class="fs-11 fleetRow {if $show_fleets_active}d-none{/if}">
      <span id="fleettime_{$index}" class="fleets" data-fleet-end-time="{$fleet.returntime}" data-fleet-time="{$fleet.resttime}">
        {pretty_fly_time({$fleet.resttime})}
      </span>
      <span id="fleettime_{$index}">{$fleet.text}</span>
    </div>
    {/foreach}

</div>
<script src="{"./scripts/game/fleetTable.js"|auto_version}"></script>
<script>
	function pruefeUndNeuladen() {
		// Zählt alle .fleetRow-Elemente, die nicht gerade ausgeblendet werden (opacity !== '0')
		const verbleibendeFlotten = Array.from(document.querySelectorAll('.fleetRow'))
		.filter(row => row.style.opacity !== '0');

		// Wenn keine Flotten mehr übrig sind, Seite neu laden
		if (verbleibendeFlotten.length === 0) {
				setTimeout(() => {
					window.location.reload();
			}, 1000); // 1 Sekunde Verzögerung, damit der Server die Daten verarbeiten kann
		}
	}

	document.querySelectorAll('.fleetRow').forEach(row => {
		const counterSpan = row.querySelector('.fleets');
		const textSpan = row.querySelector('span:not(.fleets)');

		if (counterSpan && textSpan) {
			// 1. Übergeordnetes Element als Flexbox formatieren
			row.style.display = 'flex';
			row.style.alignItems = 'flex-start';
			row.style.gap = '15px';

			// 2. Linke Spalte mit fester Breite (90px) erstellen
			const linkeSpalte = document.createElement('div');
			linkeSpalte.style.display = 'flex';
			linkeSpalte.style.flexDirection = 'column';
			linkeSpalte.style.flex = '0 0 90px';

			counterSpan.parentNode.insertBefore(linkeSpalte, counterSpan);
			linkeSpalte.appendChild(counterSpan);

			// 3. Rechte Spalte flexibel machen
			textSpan.style.flex = '1';

			// 4. Endzeit berechnen
			const endTimeStamp = Number(counterSpan.dataset.fleetEndTime) * 1000;
			const endDate = new Date(endTimeStamp);

			const heute = new Date();
			heute.setHours(0, 0, 0, 0);
			const ankunftsTag = new Date(endDate);
			ankunftsTag.setHours(0, 0, 0, 0);

			const diffTage = Math.round((ankunftsTag - heute) / (1000 * 60 * 60 * 24));

			let datumsPraefix = "";
			if (diffTage === 1) {
				datumsPraefix = "Morg. ";
			} else if (diffTage > 1) {
				datumsPraefix = endDate.toLocaleDateString('de-DE', { weekday: 'short' }) + " ";
			}

			const endZeit = endDate.toTimeString().split(' ');

			// 5. Endzeit-Element erstellen und links unten anfügen
			const endZeitAnzeige = document.createElement('div');
			endZeitAnzeige.style.fontSize = '0.85em';
			endZeitAnzeige.style.color = '#aaa';
			endZeitAnzeige.style.marginTop = '2px';
			endZeitAnzeige.textContent = datumsPraefix + endZeit[0];

			linkeSpalte.appendChild(endZeitAnzeige);

			// 6. Intervall-Check für den Ablauf dieser spezifischen Flotte
			const checkAblauf = setInterval(() => {
					if (!counterSpan) {
						clearInterval(checkAblauf);
						return;
					}
				if (counterSpan.textContent.trim() === '-') {
					clearInterval(checkAblauf); // Intervall für diese Zeile stoppen

					// Zeile sanft ausblenden
					row.style.transition = 'opacity 0.5s';
					row.style.opacity = '0';

					setTimeout(() => {
						row.remove();            // Aus dem DOM löschen
						pruefeUndNeuladen();     // Prüfen, ob die Liste jetzt komplett leer ist
					}, 500);
				}
			}, 1000);
		}
	});
</script>