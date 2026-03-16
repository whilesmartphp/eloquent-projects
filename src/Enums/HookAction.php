<?php

namespace Whilesmart\Projects\Enums;

enum HookAction: string
{
    case INDEX = 'index';
    case STORE = 'store';
    case SHOW = 'show';
    case UPDATE = 'update';
    case DESTROY = 'destroy';
    case ARCHIVE = 'archive';
    case UNARCHIVE = 'unarchive';
    case ASSIGN = 'assign';
    case UNASSIGN = 'unassign';

    public static function all(): array
    {
        return array_column(self::cases(), 'value');
    }
}
