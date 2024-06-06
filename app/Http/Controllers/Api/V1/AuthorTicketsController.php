<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\User;
use App\Models\Ticket;
use App\Policies\V1\TicketPolicy;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Resources\V1\TicketResource;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthorTicketsController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    public function index(User $author, TicketFilter $filters) {
        return TicketResource::collection(
            Ticket::where('user_id', $author->id)->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, User $author)
    {
        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes([
                'author' => $author->id
            ])));
        }

        return $this->notAuthorized('You are not authorized to create that resource');
    }

    public function replace(ReplaceTicketRequest $request, User $author, Ticket $ticket)
    {
        // PUT
        if( $this->isAble('replace', $ticket)){
            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);

        }

        return $this->error('You are not allowed to update that resource', 401);

    }

    // PATCH
    public function update(UpdateTicketRequest $request, User $user, Ticket $ticket)
    {
        if($this->isAble('update', $ticket)) {
            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);
        }

        return $this->error('You are not allowed to update that resource', 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $author, Ticket $ticket)
    {
        if($this->isAble('delete', $ticket)){
            $ticket->delete();
            return $this->ok('Ticket Successfully deleted');
        }
        return $this->error('You are not allowed to delete that resource', 401);
    }
}
