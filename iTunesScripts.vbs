Dim iTunesApp, libPlaylist, BtnCode, pops, song, sources, playlist, track, value

Set iTunesApp = WScript.CreateObject("iTunes.Application")
Set pops = WScript.CreateObject("WScript.Shell")
'unlike in a regular language we get the next thing by periods.
 
PlaylistName = "Script Test"

Set playlist = iTunesApp.sources.Item(1).playlists.ItemByName(PlaylistName).tracks
'Set track = iTunesApp.GetITObjectByID(2729222,2729222,1000345,2729222)
'value = playlist.Items(1)
' Set ListSongs=TestPlaylist.Tracks
' Set song = ListSongs.Item(1)
Wscript.Echo playlist.Item(1).name



' For Each song In playlist
'     WScript.Echo playlist.Item(22)
' Next



 
' End of FileSystemObject example: newFile VBScript