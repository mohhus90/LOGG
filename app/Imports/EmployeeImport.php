<?php

namespace App\Imports;

use App\Models\Employee;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class EmployeeImport implements ToCollection
{
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            // Employee::create([
            //     'name' => $row[0],
            // ]);
            echo $row[0];
            echo "<br>";
            
        }
    }
}