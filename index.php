<?
	include ("./config.inc");
	$sql = mysql_connect ("localhost", $DB_USER, $DB_PASS);
	mysql_select_db("surma");

	$data="" ;
	$pages="" ;

	$inputform = "<form action=\"".$PHP_SELF."\" method=\"post\">
						<tr>
						<td>Text/Links/Token:</td>
						<td><input type=\"hidden\" name=\"action\" value=\"insert\">
						<input type=\"text\" name=\"data\">
						<input type=\"text\" name=\"links\">
						<input type=\"password\" name=\"token\">
						<input type=\"submit\" value=\"Insert\"></td>
						</tr>
						</form>";

	if ($_POST["action"] == "insert" && md5($_POST["token"]) == $token)
		mysql_query("INSERT INTO dump (timestamp, data, links) VALUES (UNIX_TIMESTAMP(),'".$_POST["data"]."','".$_POST["links"]."')");	
	elseif ($_POST["action"] == "editdelete" && isset($_POST["timestamp"]) && md5($_POST["token"]) == $token)
		mysql_query ("DELETE FROM dump WHERE timestamp=".$_POST["timestamp"]);
	elseif ($_POST["action"] == "editdelete" && isset($_POST["timestamp"]) && md5($_POST["token"]) != $token)
	{

		$entry = mysql_fetch_array(mysql_query("SELECT * FROM dump where timestamp='".$_POST["timestamp"]."'"));
		$inputform = "<form action=\"$PHP_SELF\" method=\"post\">
							<tr>
							<td>Text/Links/Token:</td>
							<td><input type=\"hidden\" name=\"action\" value=\"edit2\">
							<input type=\"hidden\" name=\"timestamp\" value=\"".$entry["timestamp"]."\">
							<input type=\"text\" name=\"data\" value=\"".htmlspecialchars($entry["data"])."\">
							<input type=\"text\" name=\"links\" value=\"".$entry["links"]."\">
							<input type=\"password\" name=\"token\">
							<input type=\"submit\" value=\"Save\"></td>
							</tr>
							</form>";

	}
	elseif ($_POST["action"] == "edit2" && isset($_POST["timestamp"]) && md5($_POST["token"]) == $token)
			mysql_query("UPDATE dump SET data='".$_POST["data"]."',links='".$_POST["links"]."' WHERE timestamp='".$_POST["timestamp"]."'");

	{
		if (!isset ($_GET["page"]) || !preg_match("/[0-9]+/", $_GET["page"]) || $_GET["page"] <= 0)
			$page = 0;
		else
			$page = ($_GET["page"] - 1)*10;
		$entries = mysql_query("SELECT * FROM dump ORDER BY timestamp DESC LIMIT ".$page.",10");
		while ($fet = mysql_fetch_array($entries))
		{
			$fet["data"] = htmlspecialchars($fet["data"]);
			$links = explode("|", $fet["links"]);
			foreach ($links as $link)
				$fet["data"] = preg_replace ('/\[([^\]]+)\]/','<a href="'.$link.'" target="_blank">$1</a>', $fet["data"], 1);
			$data .= "<tr>
				<td><nobr><input type=\"radio\" name=\"timestamp\" value=\"".$fet["timestamp"]."\">".date("d.m.Y H:i", $fet["timestamp"])."</nobr></td>
				<td>".$fet["data"]."</td>
				</tr>";
		}

		$fet = (mysql_fetch_array(mysql_query("SELECT COUNT(*) FROM dump")));
		$zeros = floor(log($fet[0]/10+1,10));
		$maxpage=floor($fet[0]/10)+1;
		$actpage=$page/10+1;
		$pages .= "<a href=\"".$PHP_SELF."?page=".max(1,$actpage-1)."\">&laquo;</a> ";
		for ($i=1; $i<=$maxpage; $i++)
			$pages .= "<a href=\"".$PHP_SELF."?page=".$i."\">".sprintf("%0".($zeros+1)."d",$i)."</a> ";
		$pages .= "<a href=\"".$PHP_SELF."?page=".min($maxpage,$actpage+1)."\">&raquo;</a> ";
	}
	mysql_close($sql);
?>

<html>
<head>
	<title>Surma's Linkdump</title>
</head>
<body>
<pre>
<a href="<?=$PHP_SELF;?>">Home</a>
<table border=0>
<?=$inputform;?>
<form action="<?=$PHP_SELF;?>" method="post">
<?=$data;?>
<tr>
<td>Token:</td>
<td><input type="hidden" name="action" value="editdelete">
<input type="password" name="token">
<input type="submit" value="Edit/Delete"></td>
</tr>
</form>
<tr>
<td>Pages:</td>
<td>
<?=$pages;?>
</td>
</tr>
</table>
</pre>
</body>
</html>
