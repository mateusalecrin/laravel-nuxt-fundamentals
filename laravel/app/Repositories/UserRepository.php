<?php

namespace App\Repositories;

use App\DTO\Users\CreateUserDTO;
use App\DTO\Users\EditUserDTO;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class UserRepository
{
    public function __construct(protected User $user)
    {
    }

    public function getPaginate(int $totalPerPage = 15, int $page = 1, string $filter = ''): LengthAwarePaginator
    {
        return $this->user->with('roles')->where(function($query) use ($filter) {
            if ($filter !== '') {
                $query->where('name', 'LIKE', "%{$filter}%");
            }
        })->paginate($totalPerPage, ['*'], 'page', $page);
    }

    public function createNew(CreateUserDTO $dto): User
    {
        $data = (array) $dto;
        $data['password'] = bcrypt($data['password']);
        return $this->user->create($data);
    }

    public function findById(string $id): ?User
    {
        return $this->user->find($id);
    }

    public function findByEmail(string $email): ?User
    {
        return $this->user->where('email', $email)->first();
    }

    public function update(EditUserDTO $dto): bool
    {
        if(!$user = $this->findById($dto->id)) {
            return false;
        }

        $data = (array) $dto;
        unset($data['password']);

        if($dto->password !== null) {
            $data['password'] = bcrypt($dto->password);
        }

        return $user->update($data);
    }

    public function delete(string $id): bool
    {
        if(!$user = $this->findById($id)) {
            return false;
        }

        return $user->delete();
    }

    public function syncRoles(string $id, array $roles) : ?bool
    {
        if(!$user = $this->findById($id)) {
            return null;
        }

        $user->roles()->sync($roles);
        return true;
    }

    public function getRolesByUserId(string $id)
    {
        return $this->findById($id)->roles()->get();
    }
}
