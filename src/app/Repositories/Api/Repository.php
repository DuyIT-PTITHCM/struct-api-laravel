<?php

namespace App\Repositories\Api;

use App\Models\Model;
use Illuminate\Support\Facades\Request;
use LogicException;

abstract class Repository
{
    /**
     * @var Model
     */
    protected $model;
    protected $take = 10;

    public function __construct()
    {
        if (empty($this->model)) {
            throw new LogicException(get_class($this) . ' must have a $model');
        }

        $this->model = app()->make($this->model);
    }

    public function index($params = ["paging" => true])
    {
        $query = $this->model->filter();

        /**
         * ["order" => ["key" => value]]
         */
        if (!empty($params['orderBy'])) {
            $query->orderBy($params['orderBy'][0], (empty($params['orderBy'][1]) ? 'DESC' : $params['orderBy'][1]));
        } else {
            $query->orderBy('id', 'DESC');
        }

        /**
         * ["groupBy" => xxx]
         */
        if (!empty($params['groupBy'])) {
            $query->groupBy($params['groupBy']);
        }

        /**
         * ["pluck" => field]
         */
        if (!empty($params['pluck'])) {
            return $query->pluck($params['pluck'])->all();
        }

        if (!empty($params['paging'])) {
            return $query->paginate($this->take());
        }
        return $query->get();
    }

    /**
     * @return int
     */
    protected function take()
    {
        $take = (int)Request::input('take');
        $isExport = (int)Request::input('is_export', false);
        $take = $take ?: $this->take;
        return ($take > 50 && !$isExport) ? 50 : $take;
    }
}
