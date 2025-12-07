<?php

namespace App\Admin\Repositories;

use App\Models\SecurityLog as Model;
use Dcat\Admin\Repositories\EloquentRepository;

class SecurityLog extends EloquentRepository
{
    /**
     * Model.
     *
     * @var string
     */
    protected $eloquentClass = Model::class;
}
