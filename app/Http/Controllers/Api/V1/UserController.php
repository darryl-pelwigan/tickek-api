<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Policies\V1\UserPolicy;
use App\Http\Filters\V1\AuthorFilter;
use App\Http\Requests\Api\V1\ReplaceUserRequest;
use App\Http\Resources\V1\UserResource;
use App\Http\Requests\Api\V1\StoreUserRequest;
use App\Http\Requests\Api\V1\UpdateUserRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends ApiController
{

    protected $policyClass = UserPolicy::class;
    /**
     * Display a listing of the resource.
     */
    public function index(AuthorFilter $filters)
    {
        return UserResource::collection(
            User::filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        if($this->isAble('store', User::class)) {
            return new UserResource(User::create($request->mappedAttributes()));

        }
        return $this->notAuthorized('You are not allowed to create that resource', 401);
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        if ($this->include('tickets')) {
            return new UserResource($user->load('tickets'));
        }

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    // PATCH
    public function update(UpdateUserRequest $request, User $user)
    {
        // policy
        if($this->isAble('update', $user)) {
            $user->update($request->mappedAttributes());

            return new UserResource($user);
        }

        return $this->notAuthorized('You are not allowed to update that resource', 401);
    }

    // PUT
    public function replace(ReplaceUserRequest $request, User $user)
    {
        // policy
        if($this->isAble('replace', $user)) {
            $user->update($request->mappedAttributes());

            return new UserResource($user);
        }

        return $this->notAuthorized('You are not allowed to update that resource', 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // policy
        if($this->isAble('delete', $user)) {
            $user->delete();

            return $this->ok('User Successfully deleted');
        }

        return $this->notAuthorized('You are not allowed to delete that resource', 401);
    }
}
