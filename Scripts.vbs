' Scripts.vbs
' Sample VBScript to create a file using FileSystemObject
' Author Guy Thomas https://computerperformance.co.uk/
' Version 1.6 - August 2010
' ------------------------------------------------'

Option Explicit
Dim objFSO, objFSOText, objFolder, objShell,objFile
Dim strDirectory, strFile
strDirectory = "C:\logs"
strFile = "\Summer.txt"

'create the File System Object
Set objFSO = CreateObject("Scripting.FileSystemObject")

'Check that the strDirect folder exists
If objFSO.FolderExists(strDirectory) Then
Set objFolder = objFSO.GetFolder(strDirectory)
Else
Set objFolder = objFSO.CreateFolder(strDirectory)
Wscript.Echo "Just created " & strDirectory
End If

If objFSO.FileExists(strDirectory & strFile) Then 
Set objFolder = objFSO.GetFolder(strDirectory)
Else
Set objFile = objFSO.CreateTextFile(strDirectory & strFile)
Wscript.Echo "Just created " & strDirectory & strFile
End If

set objFolder = nothing
set objFile = nothing

If err.number = vbEmpty then
Set objShell = CreateObject("Wscript.Shell")
objShell.run ("Explorer" & " " & strDirectory & "\")
Else WScript.echo "VBScript Error: " & err.number
End If
WScript.Quit

' Create the File System Object
Set objFSO = objFSO.CreateFolder(strDirectory)

' -- The heart of the create file script
'------------------------------
'Creates the file using the value of strFile on Line 11
'--------------------------------
Set objFile = objFSO.CreateTextFile(strDirectory & strFile)
Wscript.Echo "Just created " & strDirectory & strFile

Wscript.Quit

' End of FileSystemObject example: newFile VBScript
