<!-- Applying lyrics to songs in iTunes -->
<!-- 
    1. We need to get the list of songs with basic tags we need the lyrics for. 
    2. We search for each song in Rap Genius to see if the lyrics exist
        - We can also add a check to see if it's necessary to even look for the lyrics 
    3. We put the lyrics into an array 
    4. We run a script to add the lyrics to it's appropriate song
    -->
    
    
<?php
//======================================================================
// IMPORTED METHODS
//======================================================================
//require 'test.php';

use Oefenweb\DamerauLevenshtein\DamerauLevenshtein;


require 'DamerauLevenshtein.php';
require 'GetLyrics.php';
require 'searchRapGenius.php';


//======================================================================
//  1. GET TRACKS
//======================================================================
Echo "Starting";
$output = null; // This will return an array of our tracks
$counter = 0;   // Use the counter to skip the unneeded return values, a slice on the array could also be used
$playlist = '"Recently Added"'; // This will need to be changed to an input for the playlist
$tracks = [];
exec ('cscript "GetPlaylistTracks.vbs" '.$playlist, $output);

/*
* Output will be an array of songs keep in mind the format for each song will look like this:
    SongName;;songArtist;;songAlbum;;songtrackID;;
*/

//-----------------------------------------------------
// break the song into it's sections Song Name/ Artist/ Album Name / TrackID 
//-----------------------------------------------------
foreach ($output as $song){// We'll need to split the items into an array.
    if ($counter++ <3){ // We skip the buffer array items.
        continue;
    }
    // $test = explode(';;', $song);
    $song = explode(';;', $song); // Splits the song into an array of it's parts by ;;n
    array_push($tracks, $song);
}
//======================================================================
//  2. FIND THE SONGS ON RAP GENIUS
//======================================================================
$trackLyrics = array(); // This will be the container of lyrics we currently have. Format [0] --> Track Id for Itunes [1] --> Lyrics for the song.
$cutoff= 5; // This is the value the cut off is below, we can't have a difference greater than 4.
echo '<br> Step 2 starting <br>';
if (count($tracks) > 40 ){
    ini_set('max_execution_time',count($tracks) *5 );
}
for( $i = 0; $i<count($tracks); $i++){ // we'll keep checking for the number of songs that exist.
    if($tracks[$i][4] > 100){
        continue;
    }
    $searchResults = getResults($tracks[$i]); // This gets the track information from rap genius in a JSON format.
    $hits = $searchResults[0]; // we breakdown into the search results
    $song = $searchResults[1]; 
    if ($searchResults !== false){ // Find the hits, and discriminate
        /*
         * Our foreach loop will implement a search feature that should accurately find a song 
         * but is flexible enough to account for slight differences in the account name
         * We  first check for exact matches before we account for differences
        */
        $hitsChecked = 0;
        $bestHit = [];
        $bestrating = 0;
        foreach ($hits as $hit){
            if ($hitsChecked >=3){ //The number of hits we've looked at, we'll check max 3
                if(empty($bestHit)){
                    Echo "<br> No Lyrics found for: " . $song[0] . " by " .$song[1] . " # " .$song[3];
                } else {
                    $lyrics = '"'. strip_tags(str_replace('"', '%22', $bestHit[1]), "<br>") .'"';
                    $trackID = $bestHit[0];
                    exec ('cscript "SaveLyrics.vbs" '. $playlist .' '. $trackID .' '.$lyrics, $outset);
                }
                //pushHit($bestHit);
                break;
            }
            $hitsChecked++; 
            if(strcasecmp($hit['result']['title'], $song[0]) == 0 ){ // check that the song title matches our song title.
                if(stripos($hit['result']['primary_artist']['name'], $song[1]) !== false){// check that the primary artists matches ours
                    
                    $counter = 0; // we'll count how many songs 
                    $matches = 0;
                    $score = -1;
                    if(empty($song['features'])){$score=1; } //if we have no features we just give a 1 score.
                    else{
                        if (stripos($hit['result']['primary_artist']['name'], '&') !== false){
                            $featureTitle = $hit['result']['title_with_featured'] . ' ' . $hit['result']['primary_artist']['name'];
                        }  else { 

                            $featureTitle =  $hit['result']['title_with_featured'];
                        }
                        $score = checkFeaturedArtists($song['features'], $featureTitle, $matches);
                    }
                    if(setLyrics($score, $hit, $song, $playlist)) break;
                    //if(checkScoreandPushLyrics($score, $hit, $song))break;
                } else if( !empty($song['features'])){ // In case we have the wrong primary artist and he is in the features.
                    if (stripos($song[1], ' ') > 0){ // if we have a space in the artist name we need to convert our title to ASCII
                        $TitleWithFeatures = iconv ("UTF-8", "ASCII//TRANSLIT", $hit['result']['title_with_featured']); //UTF-8 Titles will not match.
                    } else {
                        $TitleWithFeatures = $hit['result']['title_with_featured']; // no need to waste time converting if no space is in the artist name
                    }
                    if(stripos($TitleWithFeatures , $song[1]) > 0){
                        $counter = 0; // we'll count how many songs 
                        $matches = 0;
                        $featureTitle = ' ' . $hit['result']['primary_artist']['name']; // we add the primary artist to the
                        $score = checkFeaturedArtists($song['features'], $featureTitle, $matches);
                        $contains=titleContainsScore($hit, $song[0]);
                        $curRating = $matches + $cutoff +$contains - 1;
                        $bestrating = setBestRating($bestHit, $curRating, $song[3], $hit);
                    }
                }
            } else {
                $offset = checkSimilarity($hit, $song[0]);
                
                if ($offset<5){ // if our difference is less than 5 we can process the results (this difference includes not having 'the')
                    if(stripos($hit['result']['primary_artist']['name'], $song[1]) !== false){// check artist
                        $matches = 0;
                        $score = -1;
                        if(empty($song['features'])){
                            $score = 1;
                        } else {
                            if (stripos($hit['result']['primary_artist']['name'], '&') !== false){
                                $featureTitle = $hit['result']['title_with_featured'] . ' ' . $hit['result']['primary_artist']['name'];
                            } else {
                                $featureTitle = $hit['result']['title_with_featured'];
                            }
                            $score = checkFeaturedArtists($song['features'], $featureTitle, $matches);
                        }
                        if ($score >= 0.6){ // Only if accuracy is greater than 60% do we consider.
                            $value = empty($bestHit);
                            if(empty($bestHit)){ // If we don't have a bestHit and this is our first go around we set it.
                                $contains=titleContainsScore($hit, $song[0]);
                                $curRating = $matches + $cutoff - $offset +$contains;
                                $bestrating = setBestRating($bestHit, $curRating, $song[3], $hit); // we add everything up for the score, we minus the offset so a bigger value is better. we set the best rating to our current rating.
                                continue; // we go to the next hit

                            } else { // Compare the offset of this hit to the previous hit, if and choose the better one.
                                $contains=titleContainsScore($hit, $song[0]);
                                $curRating = $matches + $cutoff - $offset +$contains; // We get the current rating

                                if($curRating > $bestrating){ // if our current rating is better we set that hit as our lyrics, but ONLY if it's better and not equal
                                    $bestrating = setBestRating($bestHit, $curRating, $song[3], $hit); // set to best rating.
                                } else {
                                    continue;
                                }
                            } 
                        }
                    } else if( !empty($song['features'])){ // In case we have the wrong primary artist and he is in the features.
                        if (stripos($song[1], ' ') > 0){ // if we have a space in the artist name we need to convert our title to ASCII
                            $TitleWithFeatures = iconv ("UTF-8", "ASCII//TRANSLIT", $hit['result']['title_with_featured']); //UTF-8 Titles will not match.
                        } else {
                            $TitleWithFeatures = $hit['result']['title_with_featured']; // no need to waste time converting if no space is in the artist name
                        }
                        if(stripos($TitleWithFeatures , $song[1]) > 0){
                            $counter = 0; // we'll count how many songs 
                            $matches = 0;
                            $hit['result']['title_with_featured'] .= ' ' . $hit['result']['primary_artist']['name']; // we add the primary artist to the
                            if (stripos($hit['result']['primary_artist']['name'], '&') !== false){
                                $featureTitle = $hit['result']['title_with_featured'] . ' ' . $hit['result']['primary_artist']['name'];
                            } else {
                                $featureTitle = $hit['result']['title_with_featured'];
                            }
                            $score = checkFeaturedArtists($song['features'], $featureTitle, $matches);
                            $contains=titleContainsScore($hit, $song[0]);
                            $curRating = $matches + $cutoff - $offset +$contains - 1;
                            $bestrating = setBestRating($bestHit, $curRating, $song[3], $hit);
                        }
                    }
                } else if (stripos($hit['result']['title'], "(")/*( stripos($hit['result']['title'], "(remix)") !== false || stripos($hit['result']['title'], "(interlude)")*/ !== false){
                    $newTitle =  trim(preg_replace('/\s*\([^)]*\)/', '', $hit['result']['title'])); // str_ireplace(array(" (remix)", " (Interlude)"), '', $hit['result']['title']);
                    if(strcasecmp($newTitle, $song[0]) == 0 ){
                        if(stripos($hit['result']['primary_artist']['name'], $song[1]) !== false){// check that the primary artists matches ours
                    
                            $counter = 0; // we'll count how many songs 
                            $matches = 0;
                            $score = -1;
                            if(empty($song['features'])){$score=1; } //if we have no features we just give a 1 score.
                            else{
                                if (stripos($hit['result']['primary_artist']['name'], '&') !== false){
                                    $featureTitle = $hit['result']['title_with_featured'] . ' ' . $hit['result']['primary_artist']['name'];
                                    $score = checkFeaturedArtists($song['features'], $featureTitle, $matches);
                                } else {
                                    $featureTitle = $hit['result']['title_with_featured'];
                                }
                            }
                            if(setLyrics($score, $hit, $song, $playlist)) break;
                            //if(checkScoreandPushLyrics($score, $hit, $song, 0.9))break;
                        }
                    }
                }
            }  
        }
    }
}

//======================================================================
//  3. APPLY LYRICS
//======================================================================
ECHO " <br>COMPLETE";
// foreach ($trackLyrics as $lyrical){
//     //if($count==2){break;}
//     $lyrics = '"'. strip_tags(str_replace('"', '%22', $lyrical[1]), "<br>") .'"';
//     $trackID = $lyrical[0];

//     exec ('cscript "SaveLyrics.vbs" '. $playlist .' '. $trackID .' '.$lyrics, $outset);
//     $count++;
// }


//======================================================================
//  Helper Methods
//======================================================================

function titleContainsScore( $hit, $songTitle){
    $contains=0;
    if (stripos($hit['result']['title'], $songTitle) > 0){ // if there is a contained we can give a point
        $contains=1;
    }

    return $contains;
}
function setBestRating(&$bestHit, $curRating, $id, $hit){
    $bestHit = createTrackIDandLyrics($id, $hit); // add Lyrics

    $bestRating = $curRating; // set to best rating.
    return $bestRating;
}
function checkSimilarity($hit, $songTitle){
    $spellcheck = new DamerauLevenshtein($hit['result']['title'], $songTitle); // check title
    $offset =$spellcheck->getSimilarity(); // This will return a value for the differences of the titles. Read on DamerauLevenshtein theory.
    return $offset;
}


function pushLyrics($song, $hit){
    $TrackIDandLyrics[0] = $song[3]; // we get the iTunes Track ID that is saved in the song.
    $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']); // We get the lyrics 
    while($TrackIDandLyrics[1] == "Featuring" || $TrackIDandLyrics[1] == "Produced by"){
        $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']);
    }
    array_push($GLOBALS['trackLyrics'],$TrackIDandLyrics);
}

function pushHit($bestHit){
    if(!empty($bestHit)){ // Best Hit 
        array_push($GLOBALS['trackLyrics'],$bestHit); // We add our current hit to the list.
    }
}
/**
 *
 * Convert an object to an array
 *
 * @param Array $features Array of the featured artists on the song.
 * @param Array $hit A single hit from the array of hits from rapGenius API
 * @return int $Score the score based on the number of Feature matches.
 *
 */
function checkFeaturedArtists($features, $featureTitle, &$matches = 0){
    $counter=0;
    foreach ($features as $artist ){ //Check all the featured artists to see if they're in the track
        
        if (stripos($artist, ' ') > 0){ // if we have a space in the artist name we need to convert our title to ASCII
            $TitleWithFeatures = iconv ("UTF-8", "ASCII//TRANSLIT", $featureTitle); //UTF-8 Titles will not match.
        } else {
            $TitleWithFeatures = $featureTitle; // no need to waste time converting if no space is in the artist name
        }
        $artist = trim($artist);
        if(stripos($TitleWithFeatures , $artist) > 0){ // if the song name contains the artist...
            $counter++; // We increase the amount of artists we checked for
            $matches++; // we increase the number of matches we got.
            continue; // We can skip the rest and look at the next artist.
        }
        $counter++; // Increase the amount of artists we checked for. 
    }
    if($counter>0){ // check if we actually had features to check.
        $score = $matches/$counter; // checking the percentage of matches
        
        return $score;
    }
}

/**
 *
 * Checks to see if the score is high enough, and if it is then we push to the array and return true.
 * If the score is too low we don't push any results and return false. 
 *
 * @param Int $score Ratio of matched features to the found song.
 * @param Array $hit A single hit from the array of hits from rapGenius API
 * @param Array $song our local song where we will end up applying our lyrics.
 * @param Array $TrackLyrics Not neccessary as a param since it's global but this will hold all of our lyrics.
 * @return null No return for now but may change to string to let know whehther it matched or not.
 */
function getScoreandMatches($features, $hit){
    $counter=0;
    $matches=0;
    foreach ($features as $artist ){ //Check all the featured artists to see if they're in the track
        
        if (stripos($artist, ' ') > 0){ // if we have a space in the artist name we need to convert our title to ASCII
            $TitleWithFeatures = iconv ("UTF-8", "ASCII//TRANSLIT", $hit['result']['title_with_featured']); //UTF-8 Titles will not match.
        } else {
            $TitleWithFeatures = $hit['result']['title_with_featured']; // no need to waste time converting if no space is in the artist name
        }

        if(stripos($TitleWithFeatures, $artist) > 0){ // if the song name contains the artist...
            $counter++; // We increase the amount of artists we checked for
            $matches++; // we increase the number of matches we got.
            continue; // We can skip the rest and look at the next artist.
        }
        $counter++; // Increase the amount of artists we checked for. 
    }
    if($counter>0){ // check if we actually had features to check.
        $score = $matches/$counter; // checking the percentage of matches
        return array ($score, $matches);
    }
}

function createTrackIDandLyrics($trackId, $hit){
    $TrackIDandLyrics[0] = $trackId; // we get the iTunes Track ID that is saved in the song.
    $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']); // We get the lyrics
    while($TrackIDandLyrics[1] == "Featuring" || $TrackIDandLyrics[1] == "Produced by"){
        $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']);
    }
    return $TrackIDandLyrics; 
}

/**
 *
 * Checks to see if the score is high enough, and if it is then we push to the array and return true.
 * If the score is too low we don't push any results and return false. 
 *
 * @param Int $score Ratio of matched features to the found song.
 * @param Array $hit A single hit from the array of hits from rapGenius API
 * @param Array $song our local song where we will end up applying our lyrics.
 * @param Array $TrackLyrics Not neccessary as a param since it's global but this will hold all of our lyrics.
 * @return null No return for now but may change to string to let know whehther it matched or not.
 */
function checkScoreandPushLyrics($score, $hit, $song, $threshold = 0.6){
    if ($score >= $threshold){ // We'll only consider it a success if we have a 66% accuracy.
        $TrackIDandLyrics[0] = $song[3]; // we get the iTunes Track ID that is saved in the song.
        $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']); // We get the lyrics 
        while($TrackIDandLyrics[1] == "Featuring" || $TrackIDandLyrics[1] == "Produced by"){
            $TrackIDandLyrics[1] = parseForLyrics($hit['result']['url']);
        }
        array_push($GLOBALS['trackLyrics'],$TrackIDandLyrics); // We save the array within the lyrics array so we can use it to save to iTunes
        return true;
    }else{
        return false;
    }
}

function setLyrics($score, $hit,  $song, $playlist, $threshold = 0.6){
    if ($score >= $threshold){ // We'll only consider it a success if we have a 66% accuracy.
        $trackID = (int)$song[3];
        $lyrics = parseForLyrics($hit['result']['url']); // We get the lyrics 
        while($lyrics == "Featuring" || $lyrics == "Produced by"){
            $lyrics = parseForLyrics($hit['result']['url']);
        }
        $lyrics = '"'. strip_tags(str_replace('"', '%22', $lyrics), "<br>") .'"';
        exec ('cscript "SaveLyrics.vbs" '. $playlist .' '. $trackID .' '. $lyrics, $outset);
        return true;
    }
    return false;
}


?>