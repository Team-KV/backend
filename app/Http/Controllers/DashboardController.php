<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Event;
use App\Models\Staff;
use App\Models\Task;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
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

                return $this->sendData(['NextEvent' => $nextEvent, 'GraphData' => $graphData, 'Staff' => $staff, 'Tasks' => $tasks]);
            }

            return $this->sendBadRequest('messages.noAccessError');
        }

        return $this->sendUnauthorized('messages.unauthenticated');
    }
}
