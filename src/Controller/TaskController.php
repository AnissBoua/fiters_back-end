<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    #[Route('/tasks', name: 'allTasks', methods: ['GET'])]
    public function getTasks(): JsonResponse
    {
        $tasks = json_decode(file_get_contents("./resources/task.json"));
        return new JsonResponse($tasks, 200);
    }

    #[Route('/newtask', name: 'newTasks', methods: ['POST'])]
    public function postTasks(Request $request): JsonResponse
    {
        $tasksObject = json_decode(file_get_contents("./resources/task.json"), true);
        $listsObject = json_decode(file_get_contents("./resources/list.json"), true);

        $data = json_decode($request->getContent(), true);
        $listTasks = $listsObject["lists"][$data["listID"]]["tasks"];

        $id = array_key_last($tasksObject["tasks"]) + 1;
        $data = array("id" => $id) + $data;
        $data = $data + array("done" => false);

        $listsObject["lists"][$data["listID"]]["tasks"][array_key_last($listTasks) + 1] = $data;
        $tasksObject["tasks"][$id] = $data;
        $newTask = json_encode($tasksObject);
        $updatedListsObject = json_encode($listsObject);

        file_put_contents("./resources/task.json", $newTask);
        file_put_contents("./resources/list.json", $updatedListsObject);


        return new JsonResponse($data, 201);
    }

    #[Route('/deletetasks/{id}', name: 'deleteTask', methods: ['DELETE'])]
    public function deleteTasks($id, $deletingList = false): JsonResponse
    {
        $tasksObject = json_decode(file_get_contents("./resources/task.json"), true);

        if (!$deletingList) {
            $listsObject = json_decode(file_get_contents("./resources/list.json"), true);

            $listTasks = $listsObject["lists"][$tasksObject["tasks"][$id]["listID"]]["tasks"];

            for ($i = 1; $i <= count($listTasks); $i++) {
                if (isset($listTasks[$i]) && $listTasks[$i]["id"] == $id) {
                    unset($listsObject["lists"][$tasksObject["tasks"][$id]["listID"]]["tasks"][$i]);
                };
            }
            file_put_contents("./resources/list.json", json_encode($listsObject));
        }

        if (isset($tasksObject["tasks"][$id])) {
            unset($tasksObject["tasks"][$id]);
        } else {
            return new JsonResponse("Task not found", 404);
        }

        file_put_contents("./resources/task.json", json_encode($tasksObject));


        return new JsonResponse($tasksObject, 202);
    }
}
