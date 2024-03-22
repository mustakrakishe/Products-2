<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class NewAccessTokenResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->accessToken->id,
            'name'         => $this->accessToken->name,
            'token'        => $this->plainTextToken,
            'last_used_at' => $this->accessToken->last_used_at?->format('Y-m-d H:i:s'),
            'expired_at'   => $this->accessToken->expired_at?->format('Y-m-d H:i:s'),
            'created_at'   => $this->accessToken->created_at?->format('Y-m-d H:i:s'),
            'updated_at'   => $this->accessToken->updated_at?->format('Y-m-d H:i:s'),
            'tokenable'    => new UserResource($this->accessToken->tokenable),
        ];
    }
}
