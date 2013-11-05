<?php
include_once('settings.php');

class Getter {
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
      while ($row = $result->fetch_assoc()) {
        $players[$row['player_id']] = $row['name'];
      }
      $result->free();
    }
    
    return $players;
  }
  
  public function getPlayer($name) {
    $sql = sprintf("SELECT * FROM players WHERE name = '%s'", $name);
    
    if ($result = $this->db->query($sql)) {
      if ($result->num_rows > 0) {
        return true;
      }
    }
    
    return false;
  }
  
  public function getStats() {
    $stats = array();
    
    $sql = 'SELECT *, COUNT(ps.points) AS matches, ROUND(SUM(ps.points) / 2) AS wins,SUM(ps.points) AS score, SUM(ps.goals) AS goals, SUM(ps.goals_against) AS goals_against, ROUND(ROUND(SUM(ps.points) / 2) / COUNT(ps.points) * 100) AS ratio FROM player_stats ps INNER JOIN players p ON ps.player_id = p.player_id GROUP BY p.player_id ORDER BY ratio DESC, goals DESC, goals_against ASC';
    
    if ($result = $this->db->query($sql)) {
      while ($row = $result->fetch_assoc()) {
        $stats[$row['name']] = $row;
      }
      $result->free();
    }
    
    return $stats;
  }
}

?>