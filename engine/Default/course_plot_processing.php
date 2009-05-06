<?
// include helper funtions
require_once(get_file_loc('Plotter.class.inc'));
//include('course_plot.inc');

if (isset($var['from'])) $start = $var['from'];
else $start = $_POST['from'];
if (isset($var['to'])) $target = $var['to'];
else $target = $_POST['to'];

$account->log(5, 'Player plots to '.$target.'.', $player->getSectorID());

$container = array();
$container['url'] = 'skeleton.php';
$container['body'] = 'course_plot_result.php';

$container['Distance'] = serialize(Plotter::findDistanceToX(SmrSector::getSector($player->getGameID(),$target), SmrSector::getSector($player->getGameID(),$start), true));

forward($container);

?>
