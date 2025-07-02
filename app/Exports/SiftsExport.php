<?php

namespace App\Exports;

use App\Models\Shifts_type;
use Maatwebsite\Excel\Concerns\FromCollection;

class SiftsExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Shifts_type::all();
    }
}

