<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
//    private int $id;
//    private string $name;
//    private string $email;
//    private string $login;
//    private mixed $created_at;

    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'login' => $this->login,
            'created_at' => $this->created_at->format('Y-m-d H:i:s')
        ];
    }
}
