<?php

namespace Itstudioat\Vuedata\Enums;

enum VuedataResult: string
{
    case SUCCESS = 'SUCCESS';
    case FILE_NOT_EXISTS = 'FILE_NOT_EXISTS';
    case NOT_DATA_BLOCK = 'NOT_DATA_BLOCK';
    case PARSE_ERROR = 'PARSE_ERROR';
}
