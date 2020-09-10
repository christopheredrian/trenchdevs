<?php

namespace App\Repositories\Alumni;


use App\Account;
use App\Alumni\AlumniEvent;
use App\Exceptions\TrenchDevsWebApiException;
use App\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Throwable;

class AlumniEventsRepository
{

    /** @var Account */
    protected $account;
    /** @var User */
    protected $user;

    /**
     * AlumniEventsRepository constructor.
     * @param Account $account
     */
    public function __construct(Account $account, User $user)
    {
        $this->account = $account;
        $this->user = $user;
    }

    public function all()
    {
        return AlumniEvent::query()
            ->where('account_id', $this->account->id)
            ->orderBy('id', 'desc')
            ->paginate(10);
    }

    /**
     * @param array $data
     * @throws TrenchDevsWebApiException
     * @throws Throwable
     */
    public function upsert(array $data)
    {

        $alumniEvent = new AlumniEvent();

        $data['updated_by'] = $this->user->id;
        $data['account_id'] = $this->account->id;

        $alumniEvent->validateOrFail($data);

        $id = $data['id'] ?? null;
        $alumniEvent = AlumniEvent::query()->findOrNew($id);
        $alumniEvent->fill($data);
        $alumniEvent->saveOrFail();
    }
}
