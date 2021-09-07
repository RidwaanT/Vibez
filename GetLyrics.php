<?php
require 'simple_html_dom.php';

/*Get Lyrics from RapGenius Link
 * This will use the link that goes directly to the songs lyrics.
 */
function parseForLyrics($url){ 
    $lyricPage = file_get_html($url); // uses simple_html_dom to access page.
    if(!empty($lyricPage)){
        $lyricPage = file_get_html($url); // uses simple_html_dom to access page.
        if(empty($lyricPage->getElementByTagName('p'))){
            echo "couldn't get lyrics @ " . $url;
            return '';
        }
    }
    $lyrics = $lyricPage->getElementByTagName('p')->innertext(); // The lyrics are found within the first 'p' tag.  
    return $lyrics; 
}

function checkHitsForLyrics($results, $artist, $name){
    foreach ($results as $hits){ ## we go through the hits to see if we have the right song
        $songData = $hits['result']; ## the actual information we need is in the results portion of the hits
        $songArtists = $songData['primary_artist']['name']; #This will give us the artist who owns the song sometimes we get multiple artists(also merged artist names need to be considered)
        $songArtists = listifyArtists($songArtists); // uses searchRapGenius.php, and will create a list of all artists including the featured.
        if (in_array($artist, $songArtists)){ // if our $artist is in the array of artists then we have a match
            $songTitle = $songData['title']; // we then audit the title
            if($name == $songTitle){ // if the title matches we can get the lyrics
                $url = $songData['url']; // the link URL will be taken from the hit
                return parseForLyrics($url); 
            }  
            
        }
        
    }
}


function getLyrics($song){
   $values = getResults($song);
   $results = $values[0];
   $song = $values[1];
   $lyrics = checkHitsForLyrics($results, $song[1], $song[0]);
   
   return $lyrics;

}



?>