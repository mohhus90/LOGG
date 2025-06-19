<?php

namespace App\Exports;

use App\Models\Shifts_type;
use App\Models\Employee;
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

class EmployeeExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return Employee::all();
    }
}