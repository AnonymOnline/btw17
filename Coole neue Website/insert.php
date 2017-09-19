<?php
	
	include 'protected.php';
	
	include 'btw.php';
	

	$wl = $_POST['wahllokal'];
	header ('content-type: text/html charset=utf-8');
	$parteien = $_POST['partei'];
	$kandidaten = $_POST['kandidat'];
	
	//sql parteien abfragen
	$sql = "SELECT P_ID, P_Bezeichnung FROM Partei";
	$result = mysqli_query($conn, $sql);
	//Löschen der Stimmen die schon vorhanden sind
	$stmnt = $conn->prepare("DELETE FROM 2stimme WHERE W_ID = ? AND P_ID = ?");
	$stmnt->bind_param("ii", $wl, $tmp_p_id);
	
	$stmt = $conn->prepare("INSERT INTO 2stimme(W_ID, P_ID, 2Anzahl) VALUES (?, ?, ?)");
	$stmt->bind_param("iii", $wl, $tmp_p_id, $tmp_parteien);
	
	//Parteien Zeile für Zeile einfügen
	while ($r = $result->fetch_assoc()){
		#Variablen aus Arrays holen, um sie in prepared Statements einzufügen
		$tmp_p_id = $r["P_ID"];
		$tmp_p_bz = $r["P_Bezeichnung"];
		#parteilos
		if ($tmp_p_bz=="parteilos") {
		}
		else{
			$tmp_parteien = $parteien[$r["P_ID"]];
			if ($tmp_parteien == null) {
			}else{
				$stmnt->execute();
				$stmt->execute();
			}
		}
	}
	unset ($r);
	//Löschen der Stimmen die schon vorhanden sind
	$stmnt = $conn->prepare("DELETE FROM 1stimme WHERE W_ID = ? AND D_ID = ?");
	$stmnt->bind_param("ii", $wl, $tmp_d_id);
	
	$stmt = $conn->prepare("INSERT INTO 1stimme(W_ID, D_ID, 1Anzahl) VALUES (?, ?, ?)");
	$stmt->bind_param("iii", $wl, $tmp_d_id, $tmp_kandidaten);
	
	$sql = "SELECT D_ID FROM Direktkandidaten";
	$result = mysqli_query($conn, $sql);
	#Direktkandidaten Zeile für zeile einfügen
	while ($r = $result->fetch_assoc()){
		#Variablen aus Arrays holen, um sie in prepared Statements einzufügen
		$tmp_d_id = $r["D_ID"];
		$tmp_kandidaten = $kandidaten[$r["D_ID"]];
		if ($tmp_kandidaten == null) {
		}
		else{
			$stmnt->execute();
			$stmt->execute();
		}
	} 
	$e ="SELECT P_Bezeichnung, 2Anzahl FROM Partei P, 2stimme S WHERE
		W_ID = '".$wl."'
		AND
		P.P_ID = S.P_ID;";
	$p = mysqli_query($conn, $e);
	$e ="SELECT Vorname, Name, 1Anzahl FROM Direktkandidaten D, 1stimme S WHERE
		W_ID = '".$wl."'
		AND
		D.D_ID = S.D_ID;";
	$d = mysqli_query($conn, $e);
	echo "<html>
			<head>
			<style>
				th, td {
					padding: 5px;
				}
			</style>
			</head>
			<body>
			Eingabe erfolgreich! :D <br>
			Sie haben folgende Daten eingegeben (Bitte &uuml;berpr&uuml;fen sie diese nochmal):<br>
				<table>
				 <tr>
					<th>Parteien</th>
					<th>Stimmen</th>
				 </tr>";
	while($row = $p->fetch_assoc()) {
			echo "<tr>
					<td>".$row["P_Bezeichnung"]."</td>
					<td>".$row["2Anzahl"]."</td> 
				 </tr>
				 ";
		} 
	echo "		</table> 
				<table>
				 <tr>
					<th>Direktkandidaten</th>
					<th>Stimmen</th>
				 </tr>";
	while($row = $d->fetch_assoc()) {
			echo "<tr>
					<td>".$row["Vorname"]." ".$row["Name"]."</td>
					<td>".$row["1Anzahl"]."</td> 
				 </tr>";
		} 
	
	
	echo ' 	</table>
			<br>
			 <a href="updateauswahl.php" style="font-size:20px;">Fehler gemacht?</a> <br> 
			 <a href="insertauswahl.php" style="font-size:17px;">Weiter zum Daten eintragen!</a><br>
			
			<form action="logout.php">
				<button>logout</button>
			</form>
			</body>
		<html>';
		
	if ($_SESSION['debug']) {
		echo ("Error number ".$conn->errno." : ".$conn->error);
	}
	
	$conn->close();
?>

