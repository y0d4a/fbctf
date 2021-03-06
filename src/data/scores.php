<?hh // strict

require_once ($_SERVER['DOCUMENT_ROOT'].'/../vendor/autoload.php');

/* HH_IGNORE_ERROR[1002] */
SessionUtils::sessionStart();
SessionUtils::enforceLogin();

class ScoresDataController extends DataController {
  public async function genGenerateData(): Awaitable<void> {
    $data = array();

    $leaderboard = await Team::genLeaderboard();
    foreach ($leaderboard as $team) {
      $values = array();
      $i = 1;
      $progressive_scoreboard =
        await Progressive::genProgressiveScoreboard($team->getName());
      foreach ($progressive_scoreboard as $progress) {
        $score =
          (object) array('time' => $i, 'score' => $progress->getPoints());
        array_push($values, $score);
        $i++;
      }
      $color = substr(md5($team->getName()), 0, 6);
      $element = (object) array(
        'team' => $team->getName(),
        'color' => '#'.$color,
        'values' => $values,
      );
      array_push($data, $element);
    }

    $this->jsonSend($data);
  }
}

$scoresData = new ScoresDataController();
\HH\Asio\join($scoresData->genGenerateData());
