document.observe("dom:loaded", function() {

	if($('status')[$('status').selectedIndex].value == 1){
		$('creareseo_discontinued').up(1).hide();
		$('creareseo_discontinued_product').up(1).hide();
	} else {
		$('creareseo_discontinued').up(1).show();
		$('creareseo_discontinued_product').up(1).show();
	}

	$('status').observe("change", function(e){
		if($('status')[$('status').selectedIndex].value == 1){
			$('creareseo_discontinued').up(1).hide();
			$('creareseo_discontinued').selectedIndex = 0;
			$('creareseo_discontinued_product').up(1).hide();
		} else {
			$('creareseo_discontinued').up(1).show();
			$('creareseo_discontinued').selectedIndex = 1;
			$('creareseo_discontinued_product').up(1).show();
		}
	});

	var current = $('creareseo_discontinued')[$('creareseo_discontinued').selectedIndex].text;

	if(current != "301 Redirect to Product"){
		$('creareseo_discontinued_product').up(1).hide();
	}

	$('creareseo_discontinued').observe("change", function(e){
		if($('creareseo_discontinued')[$('creareseo_discontinued').selectedIndex].text == "301 Redirect to Product SKU"){
			$('creareseo_discontinued_product').up(1).show();
		} else {
			$('creareseo_discontinued_product').up(1).hide();
		}
	});
});