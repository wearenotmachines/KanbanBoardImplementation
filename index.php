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

$router->add("/project/{:id}", function($id) use ($kb) {
	$project = $kb->getProject($id);
	echo $project->toJSON();
});

$router->add("/project/{:id}/activate", function ($id) use ($kb) {
	$kb->makeProjectActive($id);
	$output = [];
	foreach ($kb->getActiveProjects() AS $project) {
		$output[] = $project->toArray();
	}
	echo json_encode($output, JSON_PRETTY_PRINT);
	$kb->save();
});

$router->add("/project/{:id}/deactivate", function($id) use ($kb) {
	$kb->makeProjectInactive($id);
	$output = [];
	foreach ($kb->getActiveProjects() AS $project) {
		$output[] = $project->toArray();
	}
	echo json_encode($output, JSON_PRETTY_PRINT);
	$kb->save();
});

$router->add("/project/{:id}/add-user/{:userId}", function($id, $userId) use ($kb) {
	$project = $kb->getProject($id);
	$project->addUser($userId);
	$kb->removeUserFromProjectsExcept($userId, $id);
	echo $project->toJSON();
	$kb->save();
});

$router->add("/project/{:id}/remove-user/{:userId}", function($id, $userId) use ($kb) {
	$project = $kb->getProject($id);
	$project->removeUser($userId);
	echo $project->toJSON();
	$kb->save();
});

$router->add("/project/{:id}/update", function($id) use ($kb) {
	$updatedProject = $_POST['project'];
	$kb->updateProject($id, $updatedProject);
	echo json_encode($kb->getProject($id), JSON_PRETTY_PRINT);
	$kb->save();
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

$router->add("/client/{:id}", function($id) use ($kb) {
	echo $kb->getClient($id)->toJSON();
});

$router->add("/user/{:identifier}/engaged-in", function($identifier) use ($kb) {
	$toggl = new TogglClient($kb->getUserApiToken($identifier));
	$task = $toggl->getCurrentTask();
	$project = $kb->hasProject($task['data']['pid']) ? $kb->getProject($task['data']['pid']) : null;
	if ($project) {
		$project->addUser($task['data']['uid']);
		$kb->removeUserFromProjectsExcept($task['data']['uid'],$task['data']['pid']);
	}
	if (isset($task['data']['pid']) && !$kb->isActiveProject($task['data']['pid'])) {
		$kb->makeProjectActive($task['data']['pid']);
	}
	echo json_encode($toggl->getCurrentTask(), JSON_PRETTY_PRINT);
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


$router->run();