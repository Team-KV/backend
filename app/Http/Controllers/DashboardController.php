<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Returns response with information for dashboard in JSON
     *
     * @return Response|JsonResponse
     */
    public function dashboard(): Response|JsonResponse
    {
        $user = Auth::user();
        if($user != null) {
            $client = $user->client;

            if($client != null) {
                $nextEvent = Event::getNextEventByClientID($client->id);
                $graphData = Client::getGraphData($client->id);
                $staff = Staff::getStaff();
                $tasks = Task::getActiveTasksWithExercisesByClientID($client->id);

                return $this->sendData(['Event' => $nextEvent,
                    'Graph' => $graphData,
                    'Staff' => $staff,
                    'ActiveTasks' => $tasks,
                    'Client' => $client->parent,
                    'Children' => $client->children]);
            }

            return $this->sendBadRequest('messages.noAccessError');
        }

        return $this->sendUnauthorized('messages.unauthenticated');
    }

    /**
     * Returns response with information for child dashboard in JSON
     *
     * @param $id
     * @return Response|JsonResponse
     */
    public function dashboardChild($id): Response|JsonResponse
    {
        $user = Auth::user();
        if($user != null) {
            $client = $user->client;

            if($client != null) {
                $child = Client::getClientByID($id);
                if($child == null) {
                    return $this->sendNotFound('messages.clientDoesntExistsError');
                }
                if($child->parent != null && $child->parent->id == $client->id) {
                    $nextEvent = Event::getNextEventByClientID($child->id);
                    $graphData = Client::getGraphData($child->id);
                    $staff = Staff::getStaff();
                    $tasks = Task::getActiveTasksWithExercisesByClientID($child->id);

                    return $this->sendData(['Event' => $nextEvent,
                        'Graph' => $graphData,
                        'Staff' => $staff,
                        'ActiveTasks' => $tasks,
                        'Client' => $child->parent,
                        'Children' => $child->children]);
                }

                return $this->sendConflict('messages.clientRelationError');
            }

            return $this->sendBadRequest('messages.noAccessError');
        }

        return $this->sendUnauthorized('messages.unauthenticated');
    }
}
