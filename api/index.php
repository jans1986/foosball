<?php
include_once('settings.php');

$key = isset($_GET['key']) ? $_GET['key'] : '';

$salt = API_SALT;
$password = API_PASSWORD;

$hashedPassword = crypt($password, $salt);

if ($key != $hashedPassword) {
  header('HTTP/1.0 401 Unauthorized');
  exit;
}

$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($action == '') {
  header("HTTP/1.0 404 Not Found");
} else {
  $variables = array();
  $apiCalls = new Apicalls();
  
  if (method_exists($apiCalls, $action)) {
    $return = $apiCalls->{$action}($variables);
   
    $jsonResult = json_encode($return);
    echo $jsonResult;
  } else {
    header("HTTP/1.0 404 Not Found");
  }
}

class Apicalls {
  public $db;
  
  public function __construct() {
    $this->db = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_NAME);
    if (!$this->db) {
      die('Could not connect: ' . mysql_error());
    }
  }
  
  public function listPlayers() {
    $players = array();
    
    $sql = 'SELECT * FROM players ORDER BY name ASC';
    
    if ($result = $this->db->query($sql)) {
      $n = 0;
      while ($row = $result->fetch_assoc()) {
        $n++;
        $players[$n]['naam'] = $row['name'];
        $players[$n]['id'] = $row['player_id'];
      }
      $result->free();
    }
    
    return $players;
  }
  
  public function listMatches() {
    $matches = array();
    
    $sql = 'SELECT * FROM matches ORDER BY date DESC LIMIT 0, 25';
    
    if ($result = $this->db->query($sql)) {
      while ($row = $result->fetch_assoc()) {
        $matches[$row['match_id']] = $row;
      }
      $result->free();
    }
    
    return $matches;
  }
  
  public function newPlayer($variables) {
    $inputJSON = file_get_contents('php://input');
    
    $variables = json_decode($inputJSON, true);
    
    $feedback = '';

    $playerExists = false;
    if (isset($variables['name']) && $variables['name'] != '') {
      $playerExists = $this->getPlayer($variables['name']);
    } elseif ( $variables['name'] == '' ) {
      $feedback['error'] = 'Er gaat iets mis met je invoer vriend.';
      
      return $feedback;
    }
    
    if (!$playerExists) {
      $sql = sprintf("INSERT INTO players (name) VALUES ('%s')", $variables['name']);
      
      $this->db->query($sql);
      
      $feedback['goed'] = 'De speler is aangemaakt.';
    } else {
      $feedback['fout'] = 'De speler bestaat al.';
    }
    
    return $feedback;
  }
  
  public function getStats() {
    $stats = array();
    
    $sql = 'SELECT COUNT(ps.points) AS matches, ROUND(SUM(ps.points) / 2) AS wins, ROUND(ROUND(SUM(ps.points) / 2) / COUNT(ps.points) * 100) AS ratio, p.player_id, p.name, SUM(ps.points) AS score, SUM(ps.goals) AS goals, SUM(ps.goals_against) AS goals_against FROM player_stats ps INNER JOIN players p ON ps.player_id = p.player_id GROUP BY p.player_id ORDER BY ratio DESC, goals DESC, goals_against ASC';
    
    if ($result = $this->db->query($sql)) {
      while ($row = $result->fetch_assoc()) {
        $stats[$row['name']] = $row;
      }
      $result->free();
    }
    
    return $stats;
  }
  
  public function getMatches($variables) {
    $inputJSON = file_get_contents('php://input');
    
    $variables = json_decode($inputJSON, true);
    
    $matches = array();
    
    if ($variables['player_id'] > 0) {
      $sql = sprintf("SELECT count(*) FROM matches WHERE team_1_p1 = %d OR team_1_p2 = %d OR team_2_p1 = %d OR team_2_p2 = %d", $variables['player_id'], $variables['player_id'], $variables['player_id'], $variables['player_id']);
      
      if ($result = $this->db->query($sql)) {
        if ($result->num_rows > 0) {
          $row = $result->fetch_row();
          
          $matches['totaal'] = $row[0];
        }
      }
      
      if (empty($result)) {
        $matches['fout'] = 'Er gaat iets mis.';
      }
    } else {
      $matches['fout'] = 'Er gaat iets mis.';
    }
    
    return $matches;
  }
  
  public function addMatch($variables) {
    $inputJSON = file_get_contents('php://input');
    
    $variables = json_decode($inputJSON, true);
    
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
      } else {
        $result['fout'] = 'Er gaat iets mis.';
      }
    }
    
    $result['goed'] = 'De westrijd is opgeslagen.';
    
    return $result;
  }
  
  private function getPlayer($name) {
    $sql = sprintf("SELECT * FROM players WHERE name = '%s'", $name);
    
    if ($result = $this->db->query($sql)) {
      if ($result->num_rows > 0) {
        return true;
      }
    }
    
    return false;
  }
}

?>