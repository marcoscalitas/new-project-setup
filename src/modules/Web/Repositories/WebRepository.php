<?php

namespace Modules\Web\Repositories;

use Modules\Web\Models\Web;

class WebRepository
{
    public function __construct(
        protected Web $model
    ) {}

    /**
     * Get all records.
     */
    public function all()
    {
        return $this->model->all();
    }

    /**
     * Find a record by ID.
     */
    public function find(string $id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * Create a new record.
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update an existing record.
     */
    public function update(string $id, array $data)
    {
        $record = $this->find($id);
        $record->update($data);

        return $record;
    }

    /**
     * Delete a record.
     */
    public function delete(string $id)
    {
        return $this->find($id)->delete();
    }
}
