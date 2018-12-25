<?php

namespace App\Api\Controllers;

use App\Api\Requests\UserRequest;
use App\Api\ReturnCode;
use App\Exceptions\ServiceException;
use App\Models\Token;
use App\Models\User;
use App\Scopes\StatusScope;
use App\Services\UserService;
use Illuminate\Http\Request;
use Image;

class UserController extends BaseController
{
    protected $userService;

    public function __construct(UserService $service)
    {
        $this->userService = $service;
    }

    public function test()
    {
        return $this->responseSuccess($this->user()->detail()->first());
    }

    public function login(UserRequest $request)
    {
        $this->logAccess(__METHOD__, $request->except('password'));
        $credentials = $request->only(['username', 'password']);

        if (! $user = $this->userService->authenticateUser($credentials['username'], $credentials['password'])) {
            $this->logError('登录失败: 用户名或密码错误', __METHOD__, $request->except('password'));
            return $this->responseError(__('app.invalid_username_password'), ReturnCode::INVALID_USERNAME_PASSWORD);
        }

        $token = $this->userService->generateToken($user, Token::TYPE_APP);
        $this->userService->markLoginSuccess($user);
        $this->logService('登录成功', __METHOD__, $request->except('password'), $user->username);
        return $this->responseSuccess($token);
    }

    public function refresh()
    {
        $this->logAccess(__METHOD__, \Request::input(), $this->user()->username);
        try {
            $token = $this->userService->refreshToken($this->user(), Token::TYPE_APP);
            $this->logService('token刷新成功', __METHOD__, \Request::input(), $this->user()->username);
            return $this->responseSuccess($token);
        } catch (\Exception $e) {
            $this->logError('token刷新失败: ' . $e->getMessage(), __METHOD__, \Request::input(), $this->user()->username);
            $extraInfo = $e instanceof ServiceException ?  ': ' . $e->getMessage() : '';
            return $this->responseError(__('app.failed') . $extraInfo);
        }
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $keyword = $request->get('keyword');

        $users = User::withoutGlobalScope(StatusScope::class)
            ->when($keyword, function ($query) use ($keyword) {
                $query->where('name', 'like', "%{$keyword}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return $this->responseSuccess($users);
    }

    /**
     * Update User Status By User ID.
     *
     * @param $id
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function status(Request $request, $id)
    {
        $input = $request->all();
        $user = User::withoutGlobalScope(StatusScope::class)->findOrFail($id);
        if (auth()->user()->id == $id || $user->is_admin) {
            return $this->responseError('You can\'t change status for yourself and other Administrators!', ReturnCode::INSUFFICIENT_PERMISSION);
        }

        $user->update($input);

        return $this->responseSuccess();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \App\Http\Requests\UserRequest $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(UserRequest $request)
    {
        $data = array_merge($request->all(), [
            'password' => bcrypt($request->get('password')),
            'confirm_code' => str_random(64),
        ]);

        \DB::transaction(function () use ($request, $data) {
            $user = User::create($data);

            $user->syncRoles($request->get('roles'));
        });

        return $this->responseSuccess();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function edit($id)
    {
        $user = User::withoutGlobalScopes()->findOrFail($id);

        return $this->responseSuccess($user);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int                      $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        \DB::transaction(function () use ($request, $user) {
            $user->update($request->all());

            $user->syncRoles($request->get('roles'));
        });

        return $this->responseSuccess();
    }

    /**
     * Crop Avatar.
     *
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function cropAvatar(Request $request)
    {
        $currentImage = $request->get('image');
        $data = $request->get('data');

        $image = Image::make($currentImage['relative_url']);

        $image->crop((int) $data['width'], (int) $data['height'], (int) $data['x'], (int) $data['y']);

        $image->save($currentImage['relative_url']);

        auth()->user()->update(['avatar' => $currentImage['url']]);

        return $this->responseSuccess($currentImage);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy($id)
    {
        $user = User::findOrFail($id);
        if (auth()->user()->id == $id || $user->is_admin) {
            return $this->response->withUnauthorized('You can\'t delete for yourself and other Administrators!');
        }

        $user->destroy($id);

        return $this->responseSuccess();
    }

}