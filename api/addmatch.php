<?php
include_once('settings.php');

$jsonInput = file_get_contents("php://input");

$decodeInput =json_decode($jsonInput, true);

$salt = API_SALT;
$password = API_PASSWORD;

$hashedPassword = crypt($password, $salt);

if ($decodeInput['key'] != $hashedPassword) {
  header('HTTP/1.0 401 Unauthorized');
  exit;
} else {
  $apisetter = new Apisetter();
  
  $result = $apisetter->addMatch($decodeInput);
  
  $jsonResult = json_encode($result);
  echo $jsonResult;
}

class Apisetter {
  public $db;
  
  public function __construct() {
    $this->db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$this->db) {
      die('Could not connect: ' . mysql_error());
    }
  }
  
  public function addMatch($variables) {
    if ($variables['type'] == 'onevsone') {
      if ($variables['player_one'] > 0 && $variables['player_two'] > 0 && ($variables['team_one_score'] == 10 || $variables['team_two_score'] == 10)) {
        $sql = sprintf("INSERT INTO matches (team_1_p1, team_1_p2, team_2_p1, team_2_p2, team_1_score, team_2_score, date) VALUES (%d, %d, %d, %d, %d, %d, NOW())", $variables['player_one'], 0, $variables['player_two'], 0, $variables['team_one_score'], $variables['team_two_score']);
        
        $this->db->query($sql);
        $match_id = $this->db->insert_id;
        
        $points_team_1 = ($variables['team_one_score'] == 10) ? 2 : 0;
        $points_team_2 = ($variables['team_two_score'] == 10) ? 2 : 0;
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_one'], $variables['team_one_score'], $variables['team_two_score'], $points_team_1);
        $this->db->query($sql);
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_two'], $variables['team_two_score'], $variables['team_one_score'], $points_team_2);
        $this->db->query($sql);
        
        $result['goed'] = 'De westrijd is opgeslagen.';
      } else {
        $result['fout'] = 'Er gaat iets mis.';
      }
    } elseif ($variables['type'] == 'twovstwo') {
      if ($variables['player_one'] > 0 && $variables['player_three'] > 0 && $variables['player_two'] > 0 && $variables['player_four'] > 0 && ($variables['team_one_score'] == 10 || $variables['team_two_score'] == 10) && $variables['type'] == 'twovstwo') {
        $sql = sprintf("INSERT INTO matches (team_1_p1, team_1_p2, team_2_p1, team_2_p2, team_1_score, team_2_score, date) VALUES (%d, %d, %d, %d, %d, %d, NOW())", $variables['player_one'], $variables['player_three'], $variables['player_two'], $variables['player_four'], $variables['team_one_score'], $variables['team_two_score']);
        
        $this->db->query($sql);
        $match_id = $this->db->insert_id;
        
        $points_team_1 = ($variables['team_one_score'] == 10) ? 2 : 0;
        $points_team_2 = ($variables['team_two_score'] == 10) ? 2 : 0;
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_one'], $variables['team_one_score'], $variables['team_two_score'], $points_team_1);
        $this->db->query($sql);
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_two'], $variables['team_two_score'], $variables['team_one_score'], $points_team_2);
        $this->db->query($sql);
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_three'], $variables['team_one_score'], $variables['team_two_score'], $points_team_1);
        $this->db->query($sql);
        
        $sql = sprintf("INSERT INTO player_stats (match_id, player_id, goals, goals_against, points, date) VALUES (%d, %d, %d, %d, %d, NOW())", $match_id, $variables['player_four'], $variables['team_two_score'], $variables['team_one_score'], $points_team_2);
        $this->db->query($sql);
        
        $result['goed'] = 'De westrijd is opgeslagen.';
      } else {
        $result['fout'] = 'Er gaat iets mis.';
      }
    } else {
      $result['fout'] = 'Er gaat iets mis.';
    }
    
    return $result;
  }
}

?>