<?php

namespace Modules\Core\Services;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Model;

abstract class BaseService
{
    abstract protected function model(): string;

    public function getAll(int $perPage = 15): LengthAwarePaginator
    {
        return $this->model()::paginate($perPage);
    }

    public function findById(int $id): Model
    {
        return $this->model()::findOrFail($id);
    }

    public function create(array $data): Model
    {
        return $this->model()::create($data);
    }

    public function update(int $id, array $data): Model
    {
        $model = $this->model()::findOrFail($id);
        $model->update($data);

        return $model->fresh();
    }

    public function delete(int $id): void
    {
        $this->model()::findOrFail($id)->delete();
    }
}
