<?php

namespace Tygh\Enum;

class FileFormats
{
    const CSV_FORMAT = 'C';
    const XLSX_FORMAT = 'X';

    public static function getAll()
    {
        return [
            'C' => self::CSV_FORMAT,
            'X' => self::XLSX_FORMAT,
        ];
    }
}
