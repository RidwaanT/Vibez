<?php

use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;

require 'DamerauLevenshtein.php';
require 'searchRapGenius.php';
require 'GetLyrics.php';

// $playlist = '"Script Test"';
// $tracks = [];
// $output = null; // This will return an array of our tracks
// $counter = 0;   // Use the counter to skip the unneeded return values, a slice on the array could also be used

// $lyrics = "Featuring";

// if($lyrics != "Featuring"){
//     echo "matched";
// }

if(stripos("Y.A.S. (You Ain’t Shit)", "(") !== false){
   echo trim(preg_replace('/\s*\([^)]*\)/', '', "Y.A.S. (You Ain’t Shit)"));
}
// $spellCheck = new DamerauLevenshtein('Bad Meaning Good', "Bad Meanin' Good");
// // echo $test[0];
// echo $spellCheck->getSimilarity();


// $output = null; // This will return an array of our tracks
// $counter = 0;   // Use the counter to skip the unneeded return values, a slice on the array could also be used
// $playlist = '"Script Test"'; // This will need to be changed to an input for the playlist
// $tracks = [];
// exec ('cscript "GetPlaylistTracks.vbs" '.$playlist, $output);

// foreach ($output as $song){// We'll need to split the items into an array.
//     if ($counter++ <3){ // We skip the buffer array items.
//         continue;
//     }
//     $song = explode(';;', $song);
//     array_push($tracks, $song);
// }

// for( $i = 0; $i<count($tracks); $i++){
//     $searchResults = getResults($tracks[$i]);
//     $hits = $searchResults[0];
//     $name = $searchResults[1];

//     if ($searchResults !== false){
//         echo $name;
//         echo "<br>";
//     }
// }

?>