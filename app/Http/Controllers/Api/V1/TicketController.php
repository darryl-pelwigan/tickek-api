<?php

namespace App\Http\Controllers\Api\V1;

use App\Models\Ticket;
use App\Http\Filters\V1\TicketFilter;
use App\Http\Resources\V1\TicketResource;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Policies\V1\TicketPolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketController extends ApiController
{
    protected $policyClass = TicketPolicy::class;

    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        if($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes()));

        }

        return $this->notAuthorized('You are not allowed to update that resource', 401);
    }

    /**
     * Display the specified resource.
     */
    public function show(Ticket $ticket)
    {
        if($this->include('author')) {
            return new TicketResource($ticket->load('user'));
        }
        return new TicketResource($ticket);
    }

    /**
     * Update the specified resource in storage.
     */
    // PATCH
    public function update(UpdateTicketRequest $request, Ticket $ticket)
    {
        // policy
        if($this->isAble('update', $ticket)) {
            $ticket->update($request->mappedAttributes());

            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You are not allowed to update that resource', 401);

    }

    // PUT
    public function replace(ReplaceTicketRequest $request, Ticket $ticket)
    {
        // policy
        if($this->isAble('replace', $ticket)){
            $ticket->update($request->mappedAttributes());
            return new TicketResource($ticket);
        }

        return $this->notAuthorized('You are not allowed to update that resource', 401);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Ticket $ticket)
    {
        // policy
        if($this->isAble('delete', $ticket)) {
            $ticket->delete();
            return $this->ok('Ticket Successfully deleted');
        }

        return $this->notAuthorized('You are not allowed to delete that resource', 401);
    }
}
