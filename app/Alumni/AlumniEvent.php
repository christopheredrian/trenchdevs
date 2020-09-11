<?php

namespace App\Alumni;

use App\Exceptions\TrenchDevsWebApiException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use Illuminate\Validation\Rule;

/**
 * Class AlumniEvent
 * @property $name
 * @property $account_id
 * @property $description
 * @property $place
 * @property $start_date
 * @property $end_date
 * @package App\Alumni
 */
class AlumniEvent extends Model
{
    protected $table = 'alumni_events';

    private $rules = [
        'name' => ['required', 'string', 'max:255', 'unique:alumni_events'],
        'description' => ['required', 'string', 'max:1028'],
        'location' => ['required', 'string', 'max:255'],
        'start_date' => ['date', 'nullable'],
        'end_date' => ['date', 'nullable'],
        'account_id' => ['exists:accounts,id', 'required'],
        'updated_by' => ['exists:users,id', 'required'],
    ];

    protected $fillable = [
        'name',
        'description',
        'location',
        'start_date',
        'end_date',
        'account_id',
        'updated_by',
    ];


    // https://daylerees.com/trick-validation-within-models/
    // todo: chris - can have this as a separate abstract class
    /** @var MessageBag */
    protected $errors = null;

    /**
     * @param array $data
     * @return bool
     * @throws TrenchDevsWebApiException
     */
    public function validateOrFail(array $data)
    {

        $id = $data['id'] ?? null;

        if ($id) {
            $this->rules['id'] = ['exists:alumni_events'];
            $this->rules['name'] = ['required', 'max:255', Rule::unique($this->table)->ignore($id)];
        }

        $v = Validator::make($data, $this->rules);

        if ($v->fails()) {
            throw new TrenchDevsWebApiException($v);
        }

        // validation passed
        return true;
    }

    public function getErrors()
    {
        return $this->errors;
    }


}
