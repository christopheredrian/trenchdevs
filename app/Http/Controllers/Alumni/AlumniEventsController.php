<?php

namespace App\Http\Controllers\Alumni;

use App\Alumni\AlumniEvent;
use App\Exceptions\TrenchDevsWebApiException;
use App\Http\Controllers\AuthWebController;
use App\Repositories\Alumni\AlumniEventsRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class AlumniEventsController extends AuthWebController
{

    /** @var AlumniEventsRepository */
    protected $alumniEventRepository;

    public function middlewareOnConstructorCalled(): void
    {
        parent::middlewareOnConstructorCalled();
        $this->alumniEventRepository = new AlumniEventsRepository($this->account, $this->user);
    }


    /**
     * Returns all alumni events for an account
     * @return JsonResponse
     */
    public function getAllEvents()
    {
        return response()
            ->json($this->alumniEventRepository->all());
    }

    /**
     * Get a single event
     * @param int $id
     */
    public function getEvent(int $id)
    {
        return $this->jsonResponse(self::STATUS_SUCCESS, 'Success', [
            'alumni_event' => AlumniEvent::query()->findOrFail($id)
        ]);
    }


    /**
     * @param Request $request
     * @return JsonResponse
     * @throws TrenchDevsWebApiException|Throwable
     */
    public function upsert(Request $request)
    {
        $this->alumniEventRepository->upsert($request->post());

        return $this->jsonResponse(self::STATUS_SUCCESS, 'Successfully saved event entry.');
    }
}
