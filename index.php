<?php
require_once "vendor/autoload.php";

ini_set("display_errors", "On");
error_reporting(E_ALL);

use WeAreNotMachines\Kanban\KanbanBoard;
use WeAreNotMachines\Kanban\KanbanBoardUser;
use WeAreNotMachines\Kanban\KanbanBoardClient;
use WeAreNotMachines\Kanban\KanbanBoardProject;
use WeAreNotMachines\Kanban\KanbanBoardSticker;
use WeAreNotMachines\TogglClient\TogglClient;

$kb = new KanbanBoard("data.json");

$router = new WeAreNotMachines\Utilities\Router;

$router->add("/", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
	$kb->setUsers($toggl->getAllUsers());
	print_r($kb->getUsers());
});

$router->add("/cache-data", function() use ($kb) {
	$toggl = new TogglClient("f3862117ce3d14541c6e4ed5a965a80d");
	$kb->setUsers($toggl->getAllUsers());
	$kb->setClients($toggl->getAllClients());
	$kb->setProjects($toggl->getAllProjects());
	echo $kb->save();
});

$router->add("/active-projects", function() use ($kb) {
	$output = [];
	foreach ($kb->getActiveProjects() AS $project) {
		$output[] = $project->toArray();
	}
	echo json_encode($output, JSON_PRETTY_PRINT);
});

$router->add("/project/{:id}/addSticker", function($id) use ($kb) {
	$stickerData = $_POST['sticker'];
	$kb->addStickerToProject(KanbanBoardSticker::fromArray($stickerData), $id);
	echo $kb->getProject($id)->toJSON();
	$kb->save();
});

$router->add("/project/{:id}/removeSticker", function($id) use ($kb) {
	$stickerData = $_POST['sticker'];
	$kb->removeStickerFromProject(KanbanBoardSticker::fromArray($stickerData), $id);
	echo $kb->getProject($id)->toJSON();
	$kb->save();
});

/**
 * Toggl API lookup methods follow
 */

$router->add("/list-users", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
	echo json_encode($toggl->getAllUsers(), JSON_PRETTY_PRINT);
});

$router->add("/list-clients", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
	echo json_encode($toggl->getAllClients(), JSON_PRETTY_PRINT);
});

$router->add("/list-projects", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
	echo json_encode($toggl->getAllProjects(), JSON_PRETTY_PRINT);
});	

$router->add("/toggl-directory", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
	echo json_encode($toggl->getClientsAndProjects(), JSON_PRETTY_PRINT);

});

$router->add("/user/{:identifier}/engaged-in", function($identifier) use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken($identifier));
	echo json_encode($toggl->getCurrentTask(), JSON_PRETTY_PRINT);
});

$router->run();