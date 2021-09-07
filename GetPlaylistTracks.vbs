Dim iTunesApp, libPlaylist, song, sources, playlist, length

Set iTunesApp = WScript.CreateObject("iTunes.Application") ' create the itunes object

Dim Arg, PlaylistName ' We create local variables for the argument class and the parameter variables
Set Arg = WScript.Arguments

PlaylistName = Arg(0) ' set our variable to the first argument
' index = Arg(1) ' set our Index to the second variable
set playlist = iTunesApp.sources.Item(1).playlists.ItemByName(PlaylistName).tracks
'set song = playlist.tracks.Item(index)

For Each song In playlist
    WScript.Echo song.Name & ";;" & song.Artist & ";;" & song.Album & ";;" & song.index & ";;" & len(song.lyrics) & ";;"
Next