<?php
require_once "vendor/autoload.php";

ini_set("display_errors", "On");
error_reporting(E_ALL);

use WeAreNotMachines\Kanban\KanbanBoard;
use WeAreNotMachines\Kanban\KanbanBoardUser;
use WeAreNotMachines\Kanban\KanbanBoardClient;
use WeAreNotMachines\Kanban\KanbanBoardProject;
use WeAreNotMachines\TogglClient\TogglClient;

$kb = new KanbanBoard("data.json");

$router = new WeAreNotMachines\Utilities\Router;

$router->add("/", function() use ($kb) {
	print_r($kb->toArray());
});

$router->add("/cache-data", function() use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken("alex.callard@itrm.co.uk"));
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

/**
 * Toggl API lookup methods follow
 */

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