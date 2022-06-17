<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class ListController extends AbstractController
{
    #[Route('/lists', name: 'allLists', methods: ['GET'])]
    public function getLists(): JsonResponse
    {
        $lists = json_decode(file_get_contents("./resources/list.json"));
        return new JsonResponse($lists, 200);
    }

    #[Route('/newlist', name: 'newLists', methods: ['POST'])]
    public function postLists(Request $request): JsonResponse
    {
        $listsObject = json_decode(file_get_contents("./resources/list.json"), true);
        $data = json_decode($request->getContent(), true);

        $id = array_key_last($listsObject["lists"]) + 1;
        $data = array("id" => $id) + $data;
        $data = $data + array("tasks" => []);
        $listsObject["lists"][$id] = $data;
        $newList = json_encode($listsObject);
        file_put_contents("./resources/list.json", $newList);

        return new JsonResponse($data, 201);
    }

    #[Route('/deletelists/{id}', name: 'deleteList', methods: ['DELETE'])]
    public function deleteLists($id, TaskController $taskController): JsonResponse
    {
        $listsObject = json_decode(file_get_contents("./resources/list.json"), true);

        $listTasks = $listsObject["lists"][$id]["tasks"];

        for ($i = 1; $i <= count($listTasks); $i++) {

            if (isset($listTasks[$i])) {
                $taskController->deleteTasks($listTasks[$i]["id"], true);
            }
        }

        if (isset($listsObject["lists"][$id])) {
            unset($listsObject["lists"][$id]);
        } else {
            return new JsonResponse("Task not found", 404);
        }

        file_put_contents("./resources/list.json", json_encode($listsObject));

        return new JsonResponse($listsObject, 202);
    }
}
