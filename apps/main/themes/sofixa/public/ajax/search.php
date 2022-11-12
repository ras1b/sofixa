<?php
	define("__ROOT__", $_SERVER["DOCUMENT_ROOT"]);
	require_once(__ROOT__."/apps/main/private/config/settings.php");
?>
<?php if (get("action") == "leaderboards"): ?>
	<?php if (post("username") != null && get("server")): ?>
		<?php
			$leaderboards = $db->prepare("SELECT * FROM Leaderboards WHERE id = ?");
			$leaderboards->execute(array(get("server")));
			$readLeaderboards = $leaderboards->fetch();
		?>
		<?php if ($leaderboards->rowCount() > 0): ?>
			<?php
				$mysqlTable       	= $readLeaderboards["mysqlTable"];
				$sorter           	= $readLeaderboards["sorter"];
				$dataLimit        	= $readLeaderboards["dataLimit"];
				$usernameColumn   	= $readLeaderboards["usernameColumn"];

				$tableData          = $readLeaderboards["tableData"];
				$tableTitles        = $readLeaderboards["tableTitles"];
				$tableTitlesArray   = explode(",", $tableTitles);
				$tableDataArray     = explode(",", $tableData);
			?>
			<?php if ($readLeaderboards["mysqlServer"] == '0'): ?>
				<?php
					$userOrder = $db->prepare("SELECT $usernameColumn, $tableData FROM $mysqlTable WHERE $usernameColumn = ? ORDER BY $sorter DESC");
					$userOrder->execute(array(post("username")));
				?>
			<?php else : ?>
				<?php
					try {
						$newDB = new PDO("mysql:host=".$readLeaderboards["mysqlServer"]."; port=".$readLeaderboards["mysqlPort"]."; dbname=".$readLeaderboards["mysqlDatabase"]."; charset=utf8", $readLeaderboards["mysqlUsername"], $readLeaderboards["mysqlPassword"]);
					}
					catch (PDOException $e) {
						die("<strong>MySQL connection error:</strong> ".utf8_encode($e->getMessage()));
					}
					$userOrder = $newDB->prepare("SELECT $usernameColumn, $tableData FROM $mysqlTable WHERE $usernameColumn = ? ORDER BY $sorter DESC");
					$userOrder->execute(array(post("username")));
				?>
			<?php endif; ?>
			<?php if ($userOrder->rowCount() > 0): ?>
				<?php
					$readUserOrder = $userOrder->fetch();
					$leaderboardAccount = $db->prepare("SELECT * FROM Accounts WHERE realname = ?");
					$leaderboardAccount->execute(array($readUserOrder[$usernameColumn]));
					$readLeaderboardAccount = $leaderboardAccount->fetch();
				?>
				<tr <?php echo (isset($_SESSION["login"]) && ($readUserOrder[$usernameColumn] == $readAccount["realname"])) ? 'class="active"' : null; ?>>
					<td class="text-center" style="width: 40px;">
						<?php if ($readLeaderboards["mysqlServer"] == '0'): ?>
							<?php
								$userPosition = $db->prepare("SELECT $usernameColumn FROM $mysqlTable ORDER BY $sorter DESC");
								$userPosition->execute();
							?>
						<?php else : ?>
							<?php
								$userPosition = $newDB->prepare("SELECT $usernameColumn FROM $mysqlTable ORDER BY $sorter DESC");
								$userPosition->execute();
							?>
						<?php endif; ?>
            <?php $rank = 1; ?>
						<?php foreach ($userPosition as $readUserPosition): ?>
							<?php if (($readUserPosition[$usernameColumn] == $readLeaderboardAccount["realname"]) || (strtolower($readUserPosition[$usernameColumn]) == strtolower(post("username")))): ?>
								<?php if ($rank == 1): ?>
									<strong class="text-success">1</strong>
								<?php elseif ($rank == 2): ?>
									<strong class="text-warning">2</strong>
								<?php elseif ($rank == 3): ?>
									<strong class="text-danger">3</strong>
								<?php else: ?>
									<?php echo $rank; ?>
								<?php endif; ?>
                <?php break; ?>
							<?php endif; ?>
              <?php $rank++; ?>
						<?php endforeach; ?>
					</td>
					<td class="text-center" style="width: 20px;">
            <img class="rounded-circle" src="https://minotar.net/avatar/<?php echo $readUserOrder[$usernameColumn]; ?>/20.png" alt="<?php echo $serverName." Oyuncu - ".$readUserOrder[$usernameColumn]; ?>">
					</td>
					<td>
						<a rel="external" href="/player/<?php echo $readUserOrder[$usernameColumn]; ?>"><?php echo $readUserOrder[$usernameColumn]; ?></a>
						<?php echo verifiedCircle($readLeaderboardAccount["permission"]); ?>
					</td>
					<?php foreach ($tableDataArray as $readTableDate): ?>
						<td class="text-center"><?php echo $readUserOrder[$readTableDate]; ?></td>
					<?php endforeach; ?>
				</tr>
			<?php else : ?>
				<?php die(false); ?>
			<?php endif; ?>
		<?php else: ?>
			<?php  ?>
		<?php endif; ?>
	<?php else : ?>
		<?php die(false); ?>
	<?php endif; ?>
<?php else : ?>
	<?php die(false); ?>
<?php endif; ?>
