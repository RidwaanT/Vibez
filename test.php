<?php
require 'Client.php';
require 'simple_html_dom.php';

# This will give us a list of artists from a string # this is important when we have multiple primary artists
# We want to be able to take those primary artists and split them so we can check for them
function listifyArtists($inputs){
    $list = str_replace('&', ',', $inputs );
    $list = explode(',', $list);
    foreach($list as $artist){
        trim($artist);
    }

    return $list;
}




## from Rap genius and is used for access to their API's
const CLIENT_ID = 'ej5Lvg4E1GFuvt7TTN7jsr-Duo75BYX4BtP-pCTBpdsd3T1G_eLJEx0508D1hNP5';
const CLIENT_SECRET = 'PsvXw07A5Jkh2e3bsaW5hn--Cbd-OnsmH1E758DNetc701_JOBsGMUnRHwqia2D0iia8ZLKvu9coZLfHAbxMfA';
# set on their site and hopefully redirects to our main page
const REDIRECT_URI           = 'http://localhost:3000/test.php';
# this is our client Access Token so we can use their API
$token = 'gvriA_MMvnWqc3kCGW55MzYbVP4DnAR7JJShtPxFHmyD_v4sYFZ6ot7Y-nVrMB75';
$link = "https://api.genius.com/search?q=Blue%20tint&access_token=$token";
$json = file_get_contents($link);
$arr = json_decode($json, true);

#if we get a successful status then we can process our results
if ($arr['meta']['status'] == 200){
    $arr = $arr['response']['hits']; # The JSON format 
    foreach ($arr as $hits){ ## we go through the hits to see if we have the right song
        $songData = $hits['result']; ## the actual information we need is in the results portion of the hits
        $songArtist = $songData['primary_artist']['name']; #This will give us the artist who owns the song sometimes we get multiple artists(also merged artist names need to be considered)
        $songArtist = listifyArtists($songArtist);
        if (in_array('Drake', $songArtist)){
            $songTitle = $songData['title'];
            if($songTitle == $songTitle){
                $url = $songData['url'];
                echo getLyrics($url);
            }  
            
        }
        
    }
}

function testListify($arr){
    $songData = $arr[0]['result'];
    $songArtist = $songData['primary_artist']['name']; #This will give us the artist who owns the song sometimes we get multiple artists(also merged artist names need to be considered)
    $songArtist = listifyArtists($songArtist);
    print_r($songArtist);
    if (in_array('Drake', $songArtist)){
        print ("Found Drake");
    }
    print $songArtist[0];
}

function getLyrics($url){
    $lyricPage = file_get_html($url);
    $lyrics = $lyricPage->getElementByTagName('p')->innertext();

    return $lyrics;

}

?>