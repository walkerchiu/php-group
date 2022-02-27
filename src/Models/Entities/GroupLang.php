<?php

namespace WalkerChiu\Group\Models\Entities;

use WalkerChiu\Core\Models\Entities\Lang;

class GroupLang extends Lang
{
    /**
     * Create a new instance.
     *
     * @param Array  $attributes
     * @return void
     */
    public function __construct(array $attributes = [])
    {
        $this->table = config('wk-core.table.group.groups_lang');

        parent::__construct($attributes);
    }
}
