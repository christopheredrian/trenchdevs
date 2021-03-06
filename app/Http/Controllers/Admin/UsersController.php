<?php

namespace App\Http\Controllers\Admin;

use App\Account;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\AuthWebController;
use App\Http\Controllers\Controller;
use App\Models\EmailQueue;
use App\Models\Users\UserPortfolioDetail;
use App\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class UsersController extends AuthWebController
{
    /**
     * Display a listing of the resource.
     *
     * @return Application|Factory|Response|View
     */
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        $users = User::query()
            ->orderBy('first_name')
            ->simplePaginate(30);

        return view('admin.users.index', ['users' => $users]);
    }

    protected function validator($create = true)
    {
        /** @var User $user */
        $user = Auth::user();

        $defaultValidator = [
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'is_active' => ['required', Rule::in('1', '0')],
            'role' => ['required', Rule::in($user->getAllowedRolesToManage())],
        ];

        if ($create) {
            $defaultValidator['email'] = ['required', 'string', 'email', 'max:255', 'unique:users'];
            $defaultValidator['password'] = ['required', 'string', 'min:8'];
        }

        return $defaultValidator;
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Application|Factory|Response|View
     */
    public function create()
    {
        $this->adminCheckOrAbort('Feature not enabled for account. Please contact admin if you require elevated access');

        return view('admin.users.upsert', [
            'user' => new User,
            'action' => route('users.upsert'),
            'editMode' => false,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return Application|Factory|Response|View
     */
    public function show($id)
    {

        return view('admin.users.show', ['user' => User::findOrFail($id)]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return Application|Factory|Response|View
     */
    public function edit($id)
    {
        $this->adminCheckOrAbort('Feature not enabled for account. Please contact admin if you require elevated access');

        return view('admin.users.upsert', [
            'user' => User::findOrFail($id),
            'action' => route('users.upsert'),
            'editMode' => true,
        ]);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Redirector
     * @throws ValidationException
     * @throws Throwable
     */
    public function upsert(Request $request)
    {

        $this->adminCheckOrAbort('Feature not enabled for account. Please contact admin if you require elevated access');

        $data = $request->all();

        /** @var User $user */
        if ($request->id) {

            $this->validate($request, $this->validator(false));

            unset($data['password'], $data['email']);
            /**
             * Update
             */
            $user = User::findOrFail($request->id);
            $user->fill($data);
            $user->saveOrFail();

            Session::flash('message', "Successfully updated user " . $user->name());

        } else {

            $this->validate($request, $this->validator(true));

            /**
             * Create
             */
            $user = User::create([
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'account_id' => Account::getTrenchDevsAccount()->id, // todo: can modify later
                'is_active' => $data['is_active'],
                'email_verified_at' => date('Y-m-d H:i:s'),
                'role' => $data['role'],
                'password' => $data['password'],
            ]);

            Session::flash('message', "Successfully created new user " . $user->name());
        }

        return redirect(route('users.index'));


    }

    /**
     * Admin - send password reset email to user
     * @param Request $request
     * @return Application|RedirectResponse|Redirector
     */
    public function passwordReset(Request $request)
    {
        $this->adminCheckOrAbort('Feature not enabled for account. Please contact admin if you require elevated access');

        $id = $request->id ?? null;

        if (empty($id)) {
            abort(404);
        }

        /** @var User $user */
        $user = User::findOrFail($id);

        $token = Password::getRepository()->create($user);
        $user->sendPasswordResetNotification($token);

        Session::flash('message', "Successfully sent password reset email to user " . $user->name());

        return redirect(route('users.index'));
    }

    /**
     * User changes own password
     *
     * @param Request $request
     * @return RedirectResponse
     * @throws Throwable
     * @throws ValidationException
     */
    public function changePassword(Request $request)
    {

        $passwordRule = RegisterController::PASSWORD_VALIDATION_RULE;
        $passwordRule[] = 'confirmed';

        $this->validate($request, [
            'old_password' => 'required',
            'password' => $passwordRule,
        ]);

        /** @var User $user */
        $user = Auth::user();

        if (!Hash::check($request->old_password, $user->password)) {
            return back()->withErrors(['Please enter correct current password']);
        }

        $user->password = Hash::make($request->password);
        $user->saveOrFail();

        $viewData = [
            'name' => $user->name(),
            'email_body' => 'The system detected that your password was updated. '
                . 'If you have not make this update. Please contact support at support@trenchdevs.org',
        ];

        EmailQueue::queue(
            $user->email,
            'Your password was Changed',
            $viewData,
            'emails.generic'
        );

        return back()->with('message', 'Password reset successful.');
    }

    public function account(Request $request)
    {
        $user = $request->user();

        $portfolioDetail = UserPortfolioDetail::findOrEmptyByUser($user->id);

        return view('admin.users.account', [
            'user' => $user,
            'portfolio_detail' => $portfolioDetail,
        ]);
    }
}
