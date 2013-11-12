<?php
  require_once('actions.php');
  require_once('getters.php');

  if (isset($_GET['action'])) {
    if ($_GET['action'] != '') {
      $actions = new Actions();
            
      $html = $actions->{$_GET['action']}($_POST);
    }
  }

  $getters = new Getter();
  $players = $getters->listPlayers();
?>

<!DOCTYPE html>
<html lang="nl">
  <head>
    <meta charset="utf-8" />
    <title>Foosball</title>
    
    <meta name="robots" content="noindex, nofollow">
    <link rel="stylesheet" href="styles.css" type='text/css' media='all' />
  </head>

  <body>
      <?php
      if (isset($html) && $html != '') {
        if (is_array($html)) {
          echo '<div id="error">';
          foreach ($html as $error) {
            echo $error . '<br />';
          }
          echo '</div>';
        } else {
          echo '<div id="feedback">';
          echo $html;
          echo '</div>';
        }
      }
      ?>
    <div id="container">
      <h1>Foosball League</h1>
      
      <div class="block">
        <h2>Aanmelden nieuwe speler</h2>
        <form action="?action=newPlayer" method="POST">
          <input type="text" name="name" value="" placeholder="Naam" />
          <input type="submit" name="addNewPlayer" value="Aanmelden" />
        </form>
      </div>
      
      <div class="block">
        <h2>Voeg wedstrijd toe</h2>
        <form action="?action=addMatch" method="POST">
          <fieldset>
            <legend>Team 1</legend>
            <label for="player-one">Speler 1</label>
            <select name="player-one" id="player-one">
              <option value="0">Selecteer speler</option>
            <?php
              foreach ($players as $player_id => $player) {
                echo '<option value="'. $player_id .'"'. (isset($_POST['player-one']) ? ($_POST['player-one'] == $player_id) ? ' selected="selected"' : '' : '') .'>'. $player .'</option>';
              }
            ?>
            </select>
            <label for="player-three">Speler 3</label>
            <select name="player-three" id="player-three">
              <option value="0">Selecteer speler</option>
            <?php
              foreach ($players as $player_id => $player) {
                echo '<option value="'. $player_id .'"'. (isset($_POST['player-three']) ? ($_POST['player-three'] == $player_id) ? ' selected="selected"' : '' : '') .'>'. $player .'</option>';
              }
            ?>
            </select>
            <label for="team1">Score &nbsp;&nbsp;&nbsp;</label>
            <select name="team_1_score" id="team1">
              <?php
                for ($i = 0; $i < 11; $i++) {
                  echo '<option'. (isset($_POST['team_1_score']) ? $_POST['team_1_score'] == $i ? ' selected="selected"' : '' : '') .'>'. $i .'</option>';
                }
              ?>
            </select>
          </fieldset>
          <fieldset>
            <legend>Team 2</legend>
            <label for="player-two">Speler 2</label>
            <select name="player-two" id="player-two">
              <option value="0">Selecteer speler</option>
            <?php
              foreach ($players as $player_id => $player) {
                echo '<option value="'. $player_id .'"'. (isset($_POST['player-two']) ? ($_POST['player-two'] == $player_id) ? ' selected="selected"' : '' : '') .'>'. $player .'</option>';
              }
            ?>
            </select>
            <label for="player-four">Speler 4</label>
            <select name="player-four" id="player-four">
              <option value="0">Selecteer speler</option>
            <?php
              foreach ($players as $player_id => $player) {
                echo '<option value="'. $player_id .'"'. (isset($_POST['player-four']) ? ($_POST['player-four'] == $player_id) ? ' selected="selected"' : '' : '') .'>'. $player .'</option>';
              }
            ?>
            </select>
            <label for="team2">Score &nbsp;&nbsp;&nbsp;</label>
            <select name="team_2_score" id="team2">
              <?php
                for ($i = 0; $i < 11; $i++) {
                  echo '<option'. (isset($_POST['team_2_score']) ? $_POST['team_2_score'] == $i ? ' selected="selected"' : '' : '') .'>'. $i .'</option>';
                }
              ?>
            </select>
          </fieldset>
          <input type="submit" name="addMatch" value="Toevoegen" />
        </form>
      </div>

      <div class="block">
        <h2>Statistieken</h2>
        <?php
        
        $stats = $getters->getStats();

        echo '<table>';
        echo '  <tr>';
        echo '    <th>Naam</th>';
        echo '    <th>Wedstrijden gespeeld</th>';
        echo '    <th>Gewonnen wedstrijden</th>';
        echo '    <th>Verloren wedstrijden</th>';
        echo '    <th>Ratio</th>';
        echo '    <th>Doelsaldo</th>';
        echo '    <th>Doelpunten voor</th>';
        echo '    <th>Doelpunten tegen</th>';
        echo '    <th>Punten</th>';
        echo '  </tr>';
        
        foreach ($stats as $player => $stat) {
          $saldo = $stat['goals'] - $stat['goals_against'];
          
          echo '<tr>';
          echo '  <td>'. $player .'</td>';
          echo '  <td>'. $stat['matches'] .'</td>';
          echo '  <td>'. $stat['wins'] .'</td>';
          echo '  <td>'. ($stat['matches'] - $stat['wins']) .'</td>';
          echo '  <td>'. $stat['ratio'] .'%</td>';
          echo '  <td>'. (($saldo > 0) ? '+' : '') . $saldo .'</td>';
          echo '  <td>'. $stat['goals'] .'</td>';
          echo '  <td>'. $stat['goals_against'] .'</td>';
          echo '  <td>'. $stat['score'] .'</td>';
          echo '</tr>';
        }
        echo '</table>';
        ?>
      </div>
    </div>
  </body>
</html>
