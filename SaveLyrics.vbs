Dim iTunesApp, pops, song, Lyrics, test

Set iTunesApp = WScript.CreateObject("iTunes.Application") ' create the itunes object
Set pops = WScript.CreateObject("WScript.Shell")

Dim Arg, PlaylistName, index ' We create local variables for the argument class and the parameter variables

Set Arg = WScript.Arguments

PlaylistName = Arg(0) 
TrackIndex = Arg(1) 
Lyrics =  Arg(2)
Lyrics = Replace(Lyrics, "<br>", vbCrLf)
Lyrics = Replace(Lyrics, "%22", """")

Set song = iTunesApp.sources.Item(1).playlists.ItemByName(PlaylistName).tracks.Item(TrackIndex)

song.lyrics = Lyrics ' It is possible for a track to be unmodifiable.