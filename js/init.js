L.mapbox.accessToken = 'pk.eyJ1IjoiZ2Vla21hcCIsImEiOiJjaWVyanVoOG8wMGJvdTBtOGN4anBzaGNsIn0.BPQfCqdNpd3K0-mE2AIvHQ';

var map = L.mapbox
	.map('map', 'mapbox.streets')
	.setView([54.559322, -4.174804], 6);
	
var JsonLayer = map.featureLayer.loadURL('test_geo.json');

JsonLayer.eachLayer(function(layer) {
	var content = '<h1>' + layer.feature.properties.name + '</h1>';
	if (typeof layer.feature.properties.URL != 'undefined') {
		content = '<h1><a target="_blank" href="' + layer.feature.properties.URL + '">' + layer.feature.properties.name + '</a></h1>';
	}

	layer.bindPopup(content);
});

