<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class BaseRepository
{
    /**
     * The model instance.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    protected $model;

    /**
     * Create a new repository instance.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * Get all of the models from the database.
     *
     * @param array|string $columns
     * @return \Illuminate\Database\Eloquent\Collection|static[]
     */
    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * Find a model by its primary key.
     *
     * @param  mixed  $id
     * @param array|string $columns
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|static[]|static|null
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Create a new model instance that is saved to the database.
     *
     * @param  array  $data
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function create(array $data)
    {
        return $this->model->create($data);
    }

    /**
     * Update a record in the database.
     *
     * @param  mixed  $id
     * @param  array  $data
     * @param  string  $attribute
     * @return bool|int
     */
    public function update($id, array $data, $attribute = "id")
    {
        return $this->model->where($attribute, '=', $id)->update($data);
    }

    /**
     * Delete a record from the database.
     *
     * @param  mixed  $id
     * @return int
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    public function insertOrUpdate(array $data, array $uniqueKeys): void
    {
        $query = $this->model->newQuery();

        foreach ($uniqueKeys as $key) {
            if (!isset($data[$key])) {
                throw new InvalidArgumentException("Missing key '$key' from data array.");
            }

            $query->where($key, $data[$key]);
        }

        $model = $query->firstOrNew($data);
        $model->fill($data);
        $model->save();
    }
}
