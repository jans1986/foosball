<?php
include_once('settings.php');
include_once('getters.php');

class Actions {
  public $db;
  public $getter;
  public $root = BASE_URL;
  
  public function __construct() {
    $this->getter = new Getter();
    
    $this->db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$this->db) {
      die('Could not connect: ' . mysql_error());
    }
  }
  
  public function newPlayer($variables) {
    $html = '';

    $playerExists = false;
    if (isset($variables['name']) && $variables['name'] != '') {
      $playerExists = $this->getter->getPlayer($variables['name']);
    } elseif ( $variables['name'] == '' ) {
      return $html;
    }
    
    if (!$playerExists) {
      $sql = sprintf("INSERT INTO players (name) VALUES ('%s')", $variables['name']);
      
      $this->db->query($sql);
      
      $html .= 'De speler is aangemaakt.';
    } else {
      $html .= 'De speler bestaat al.';
    }
    
    return $html;
  }
  
  public function addMatch($variables) {
    $errors = array();
    
    $playerOne = $variables['player-one'];
    $playerTwo = $variables['player-two'];
    $playerThree = $variables['player-three'];
    $playerFour = $variables['player-four'];
    $team_one_score = $variables['team_1_score'];
    $team_two_score = $variables['team_2_score'];
    
    if ($playerOne == 0) {
      $errors['player-one'] = 'Selecteer speler 1';
    }
    if ($playerTwo == 0) {
      $errors['player-two'] = 'Selecteer speler 2';
    }
    
    if (empty($errors)) {
      if (($playerThree == 0 && $playerFour > 0) || ($playerThree > 0 && $playerFour == 0)) {
        $errors['num_players'] = 'Twee tegen &eacute;&eacute;n spelen is wat oneerlijk vind je niet?';
      }
    }
    
    if (empty($errors)) {
      if ($playerThree > 0 && $playerFour > 0) {
        if (($playerOne == $playerTwo) || ($playerOne == $playerThree) || ($playerOne == $playerFour) || ($playerTwo == $playerThree) || ($playerTwo == $playerFour) || ($playerThree == $playerFour)) {
          $errors['duplicate_players'] = 'Ja hallo, je kan natuurlijk niet tegen jezelf spelen vriend!';
        }
      } else {
        if ($playerOne == $playerTwo) {
          $errors['duplicate_players'] = 'Ja hallo, je kan natuurlijk niet tegen jezelf spelen vriend!';
        }
      }
    }
    
    if (empty($errors)) {
      if ($team_one_score != 10 && $team_two_score != 10) {
        $errors['score'] = 'Er moet wel iemand op 10 punten komen om te winnen h&eacute;!';
      }
    }
    
    if (empty($errors)) {
      $sql = sprintf("INSERT INTO matches (team_1_p1, team_1_p2, team_2_p1, team_2_p2, team_1_score, team_2_score, date) VALUES (%d, %d, %d, %d, %d, %d, NOW())", $playerOne, $playerThree, $playerTwo, $playerFour, $team_one_score, $team_two_score);
      
      $this->db->query($sql);
      $match_id = $this->db->insert_id;
      
      $points_team_1 = ($team_one_score == 10) ? 2 : 0;
      $points_team_2 = ($team_two_score == 10) ? 2 : 0;
      
      $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $playerOne, $team_one_score, $team_two_score, $points_team_1);
      $this->db->query($sql);
      
      $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $playerTwo, $team_two_score, $team_one_score, $points_team_2);
      $this->db->query($sql);
      
      if ($playerThree > 0 && $playerFour > 0) {
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $playerThree, $team_one_score, $team_two_score, $points_team_1);
        $this->db->query($sql);
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $playerFour, $team_two_score, $team_one_score, $points_team_2);
        $this->db->query($sql);
      }
      
      header('Location: '. $this->root);
    } else {
      return $errors;
    }
  }
}

?>