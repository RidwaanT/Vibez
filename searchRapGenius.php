<?php

require 'Client.php';

//======================================================================
// RAP GENIUS API CONSTANTS
//======================================================================
#Used for access to their API's

const CLIENT_ID = 'ej5Lvg4E1GFuvt7TTN7jsr-Duo75BYX4BtP-pCTBpdsd3T1G_eLJEx0508D1hNP5';
# We have the Redirect go back to this page
const REDIRECT_URI = 'http://localhost:3000/test.php';
# this is our client Access Token so we can use their API
global $token;
const Token = 'gvriA_MMvnWqc3kCGW55MzYbVP4DnAR7JJShtPxFHmyD_v4sYFZ6ot7Y-nVrMB75';
// $link = "https://api.genius.com/search?q=Blue%20tint&access_token=$token";



/* Turn Artist string into a List
 * This will give us a list of artists from a string delimited by '&'. This is important
 * when we have multiple primary artists so we can split them up during search.
*/
function totArtistList($inputs){
    $list = str_replace(' & ', ',', $inputs );
    $list = explode(',', $list);
    foreach($list as $artist){
        trim($artist);
    }
    return $list;
}

function getSearchLink($searchString, $token){

    $searchString = urlencode($searchString);
    $link = "https://api.genius.com/search?q=$searchString&access_token=$token";

    return $link;
}

/* Search Results
 * Returns an array of all the hits from the search
 * The most accurate hit appears first in the array
*/
function getSearchResults($link){
    $json = file_get_contents($link);
    $arr = json_decode($json, true);
    return $arr; // if error is reported the array would only have an error report
}

/* Returns Status
 * This will check the status of the search results and return true
 * if the search was successful and false if it was a failure
*/
function checkStatus($results){
    if ($results['meta']['status'] == 200){ // A 200 status represents success
        return true;
    }
    return false;
}

/* Return Search Hits
 * this will return the direct results from RapGenius 
 */
function getHits($results){
    $results = $results['response']['hits']; # The JSON format 
    return $results;
}


/*Organize Artists in Songs
 * This will take the featured section out of a song and add it to the artists section
 */
function organizeFeatures($song){
    //$pos = stripos($song[0],'feat.' ); // Check for feat. in the song title and find the start point

    if (stripos($song[0],'feat.' ) !== false){
        $pos = stripos($song[0],'feat.');
        $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
        if($featured[-1] == ')' ||  $featured[-1] ==']'){ // remove any trailing brackets
            $featured = substr($featured, 6, -1); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
        } else {
            $featured = substr($featured, 6, strlen($featured)); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
        }
        $featured = totArtistList($featured);
        $song['features'] = $featured;
        $song[0] = $title; // WE replace the song name to be without features
    } else if (stripos($song[0],'ft.') !== false){
        $pos = stripos($song[0],'ft.');
        $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
        if($featured[-1] == ')' ||  $featured[-1] ==']'){ // we remove any trailing brackets.
            $featured = substr($featured, 4, -1); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
        } else {
            $featured = trim(substr($featured, 4, strlen($featured))); // we cut the featuring 'artist' to just 'artist'
            
            $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
            
        }
        $featured = totArtistList($featured);
        $song['features'] = $featured;
        $song[0] = $title;
    } else if (stripos($song[0],' ft') !== false || stripos($song[0],'(ft') !== false || stripos($song[0],'[ft') !== false){
        $pos = stripos($song[0],'ft');
        $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
            
        if($featured[-1] == ')' ||  $featured[-1] ==']'){ // we remove any trailing brackets.
            $featured = substr($featured, 3, -1); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
        } else {
            $featured = trim(substr($featured, 3, strlen($featured))); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
        }
        $featured = totArtistList($featured);
        $song['features'] = $featured; 
        $song[0] = $title;
    } else if (stripos($song[0],'featuring') !== false){
        $pos = stripos($song[0],'featuring');
        $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
        if($featured[-1] == ')' ||  $featured[-1] ==']'){ // remove any trailing brackets
            $featured = substr($featured, 10, -1); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
        } else {
            $featured = substr($featured, 10, strlen($featured)); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
        }
        $featured = totArtistList($featured);
        $song['features'] = $featured;
        $song[0] = $title; // WE replace the song name to be without features
    } else if (stripos($song[0],'feat') !== false){
        $pos = stripos($song[0],'feat');
        $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
        if($featured[-1] == ')' ||  $featured[-1] ==']'){ // remove any trailing brackets
            $featured = substr($featured, 5, -1); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
        } else {
            $featured = substr($featured, 5, strlen($featured)); // we cut the featuring 'artist' to just 'artist'
            $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
        }
        $featured = totArtistList($featured);
        $song['features'] = $featured;
        $song[0] = $title; // WE replace the song name to be without features
    }
    /************************************* */
    // if($pos === false){ // incase we don't find feat. in the song title. we move to the next case
    //     $pos = stripos($song[0],'ft.'); //check for ft. this is another option we might come across
    //     if ($pos !== false){ // if we find 'ft.' in the title
    //         $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
            
    //         if($featured[-1] == ')' ||  $featured[-1] ==']'){ // we remove any trailing brackets.
    //             $featured = substr($featured, 4, -1); // we cut the featuring 'artist' to just 'artist'
    //             $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
    //         } else {
    //             $featured = trim(substr($featured, 4, strlen($featured))); // we cut the featuring 'artist' to just 'artist'
    //             $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
    //         } 
    //         Echo $title;
    //         $song[0] = $title;
    //     }
    // } else if ($pos !== false) { // notice that our position is 5 here because feat. is longer.
    //     $featured = substr($song[0], $pos, strlen($song[0])); // We take the featuring 'artist' substring
    //     if($featured[-1] == ')' ||  $featured[-1] ==']'){ // remove any trailing brackets
    //         $featured = substr($featured, 6, -1); // we cut the featuring 'artist' to just 'artist'
    //         $title = trim(substr($song[0], 0 , $pos-2)); // We have the title stripped of features but also remove the '('
    //     } else {
    //         $featured = substr($featured, 6, strlen($featured)); // we cut the featuring 'artist' to just 'artist'
    //         $title = trim(substr($song[0], 0 , $pos-1)); // We have the title stripped of features
    //     }
    //     $featured = totArtistList($featured);
    //     $song['features'] = $featured;
    //     Echo "We did the features thing";
    //     //echo print_r($song); 
    //     $song[0] = $title; // WE replace the song name to be without features
    // }
     return $song; // we return our edited song for Rap Genius
}

/* Search for song results - read for return details.
 * This will take a song and get the hits from the search results
 * The shape of the returned Array looks like this
 * 
 * Array(
 *      Hits:
 *          [0]: Highlights:[]
 *          index: "song"
 *          type: "song"
 *          result:
 *              Annotation_count: 11
 *              api_path: "/songs/3807754"
 *              full_title: "Blue Tint by Drake"
    *               header_image_thumbnail_url: 
    *               header_image_url: "https://images.genius.com/5c677b7bfc2f696abf2e2d928301bb44.1000x1000x1.jpg",
    *               id: 3807754,
    *               lyrics_owner_id: 5411054,
    *               lyrics_state: "complete",
    *               path: "/Drake-blue-tint-lyrics",
    *               pyongs_count: 24,
    *               song_art_image_thumbnail_url: "https://images.genius.com/5c677b7bfc2f696abf2e2d928301bb44.300x300x1.jpg",
    *               song_art_image_url: "https://images.genius.com/5c677b7bfc2f696abf2e2d928301bb44.1000x1000x1.jpg",
    *               +stats: {...},
    *               title: "Blue Tint",
    *               title_with_featured: "Blue Tint",
    *               url: "https://genius.com/Drake-blue-tint-lyrics",
    *               primary_artist:
    *                    // Check https://docs.genius.com/#/authentication-h1 for more details
 *              
 *              
 * );
 */
function getResults($song){
    $geniusSong= organizeFeatures($song); // this will remove Ft. or feat. and artists from title for a better search
    $searchString = $geniusSong[0] . ' ' . $geniusSong[1]; // We use the song title and primary artist for the search
    $url = getSearchLink($searchString, Token); // we create a URL using our search string and Token
    $results = getSearchResults($url); // We get the Search results from the API
    if (checkStatus($results)){ // Double check to see if our request worked
        $hits = getHits($results); // The original return from RapGenius has more than we need, this strips down to our main content.
        $values = [];
        array_push($values, $hits, $geniusSong);
        return $values; // send to the caller
    } else {
        return false;
    }
}
?>