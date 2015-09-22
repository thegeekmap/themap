<?php

// looks at all the RST files for GEO info and builds the geo.json file

$placesDir = '../places/';

// start out geoJSON variable.
$geoJSON = array(
	'type'		=>	'FeatureCollection',
	'features'	=>	array(),
	);

$files = getAllFiles($placesDir);

foreach ($files as $file) {
	$lines = file($file);
	$title = "";
	while ($title == "") {
		$line = array_shift($lines);
		if ($line !== "") {
			$title = trim($line);
		}
	}

	$geoData = extractGeo($lines);

	if ($geoData !== false) {
		$geo = array(
			'type'			=>	'Feature',
			'geometry'		=>	array(
				'coordinates'	=>	array($geoData['Longitude'], $geoData['Latitude']),
				'type'			=>	'Point',
				),
			'properties'	=>	array(
				'URL'			=>	$geoData['URL'],
				'marker-symbol'	=>	$geoData['Symbol'],
				'marker-color'	=>	'#ffba00', // we'll need some intelligence here soon
				'name'			=>	$title,
				)
			);
		$geoJSON['features'][] = $geo;
	}
}

file_put_contents('js/geo.json', json_encode($geoJSON, JSON_NUMERIC_CHECK | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));

function extractGeo($lines)
{
	$data = array();

	$foundTable = false;
	$foundGeoTable = false;
	$columnWidths = array();

	foreach ($lines as $line) {
		if ($foundTable) {
			// we have a table directive, is it the right one?
			if (strpos($line, ':class: geo') !== false) {
				$foundGeoTable = true;
			}

			$foundTable = false;
			continue;

		} elseif ($foundGeoTable) {
			// Ok, we have a geo table directive, let's make sure we are still in it
			if (trim($line) == '') {
				continue;
			} elseif (substr($line, 0, 3) != '   ') {
				$foundGeoTable = false;
				break;
			}

			// we haven't actually seen the table yet
			if (count($columnWidths) == 0) {
				if (trim($line) == '') {
					continue;
				} else {
					$parts = explode(' ', trim($line));
					foreach ($parts as $part) {
						if (strlen($part) > 0) {
							$columnWidths[] = strlen($part);
						}
					}
					continue;
				}
			}

			// ok, we are into the table now - let's get the data
			$line = trim($line);
			$key = trim(substr($line, 0, $columnWidths[0]));
			$line = trim(substr($line, $columnWidths[0]));
			$value = trim(substr($line, 0, $columnWidths[1]));

			// ignore the bottom row
			if (substr_count($key, '=') != $columnWidths[0]) {
				$data[$key] = $value;
			}
		} elseif (strpos($line, '.. table::') !== false) {
			$foundTable = true;
			continue;
		}
	}

	// did we get anything?  return it if so
	if (count($data) > 0) {
		return $data;
	}
	else {
		return false;
	}
}



function getAllFiles($path, $extension = array("rst"))
{
	$allFiles = array_diff(scandir($path), array('..', '.'));

	$files = array();

	foreach ($allFiles as $file) {
		if (is_dir($path . $file)) {
			$files = array_merge($files, getAllFiles($path . $file . '/'));
		}
		$extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

		if ($extension == "rst") {
			$files[] = $path . $file;
		}
	}

	return $files;
}