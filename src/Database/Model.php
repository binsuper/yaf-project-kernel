<?php

namespace Gino\Yaf\Kernel\Database;

use Illuminate\Database\Eloquent\Model as EloquentModel;

/**
 * @mixin \Illuminate\Database\Eloquent\Builder;
 */
class Model extends EloquentModel {

    public function __construct(array $attributes = []) {
        parent::__construct($attributes);
    }

}