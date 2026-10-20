/*
$(function() {
	window.setInterval(function() {
		$('.fleets').each(function() {
			var s = $(this).data('fleet-time') - (serverTime.getTime() - startTime) / 1000;
			if(s <= 0) {
				$(this).text('-');
			} else {
				$(this).text(GetRestTimeFormat(s));
			}
		})
	}, 1000);
});
*/

setInterval(() => {
	const timePassed = (serverTime.getTime() - startTime) / 1000;
	const fleetElements = document.querySelectorAll('.fleets');

	fleetElements.forEach(element => {
		// dataset liest automatisch 'data-fleet-time' aus
		const fleetTime = parseFloat(element.dataset.fleetTime);

		if (isNaN(fleetTime)) return; // Sicherheitscheck, falls das Attribut fehlt

		const remainingTime = fleetTime - timePassed;

		element.textContent = remainingTime <= 0 ? '-' : GetRestTimeFormat(remainingTime);
	});
}, 1000);


	
  function showHideFleets(){

    $.ajax({
        type: "POST",
        url: 'game.php?page=fleetTableSettings&mode=changeVisibility&ajax=1',
        success: function(data)
        {

          if ($('.fleetRow').hasClass('d-none')) {
            $('.fleetRow').removeClass('d-none')
          }else {
            $('.fleetRow').addClass('d-none')
          }

        }

    });

  }