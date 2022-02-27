<?php

namespace WalkerChiu\Group\Models\Entities;

use WalkerChiu\Group\Models\Entities\Group;
use WalkerChiu\MorphImage\Models\Entities\ImageTrait;

class GroupWithImage extends Group
{
    use ImageTrait;
}
