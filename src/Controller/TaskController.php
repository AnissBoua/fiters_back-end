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
        $data = json_decode($request->getContent(), true);

        $id = array_key_last($tasksObject["tasks"]) + 1;
        $data = array("id" => $id) + $data;

        $tasksObject["tasks"][$id] = $data;
        $newTask = json_encode($tasksObject);
        file_put_contents("./resources/task.json", $newTask);

        return new JsonResponse($data, 201);
    }

    #[Route('/deletetasks/{id}', name: 'deleteTask', methods: ['DELETE'])]
    public function deleteTasks($id): JsonResponse
    {
        $tasksObject = json_decode(file_get_contents("./resources/task.json"), true);

        if (isset($tasksObject["tasks"][$id])) {
            unset($tasksObject["tasks"][$id]);
        } else {
            return new JsonResponse("Task not found", 404);
        }

        file_put_contents("./resources/task.json", json_encode($tasksObject));

        return new JsonResponse($tasksObject, 202);
    }
}
