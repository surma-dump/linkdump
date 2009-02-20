<html>
<head>
	<title>Surma's Linkdump</title>
</head>
<body>
<pre>
<table border=0>
<form action="<?=$PHP_SELF;?>" method="post">
<tr>
<td>Text/Links/Token:</td>
<td><input type="hidden" name="action" value="insert">
<input type="text" name="data">
<input type="text" name="links">
<input type="password" name="token">
<input type="submit" value="Insert"></td>
</tr>
</form>
<form action="<?=$PHP_SELF;?>" method="post">
<?
	$token="su4wi";
	$sql = mysql_connect ("localhost", "surma", "su4wi");
	mysql_select_db("surma");
	if ($_POST["action"] == "insert" && $_POST["token"] == $token)
	{
		mysql_query("INSERT INTO dump (timestamp, data, links) VALUES (UNIX_TIMESTAMP(),'".$_POST["data"]."','".$_POST["links"]."')");	
	}
	elseif ($_POST["action"] == "delete" && $_POST["token"] == $token)
	{
		foreach ($_POST as $key => $val)
			if (preg_match ("/[0-9]+/", $key))
				mysql_query ("DELETE FROM dump WHERE timestamp=".$key);;
	}

	{
		$entries = mysql_query("SELECT * FROM dump ORDER BY timestamp DESC LIMIT 50");

		while ($fet = mysql_fetch_array($entries))
		{
			$links = explode("|", $fet["links"]);
			foreach ($links as $link)
				$fet["data"] = preg_replace ('/\[([^\]]+)\]/','<a href="'.$link.'" target="_blank">$1</a>', $fet["data"], 1);
			echo "<tr>
				<td><nobr><input type=\"checkbox\" name=\"".$fet["timestamp"]."\" value=\"1\">".date("d.m.Y H:i", $fet["timestamp"])."</nobr></td>
				<td>".$fet["data"]."</td>
				</tr>";
		}

	}
	mysql_close($sql);
?>
<tr>
<td>Token:</td>
<td><input type="hidden" name="action" value="delete">
<input type="password" name="token">
<input type="submit" value="Delete"></td>
</tr>
</form>
</table>
</pre>
</body>
</html>
