<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about() {
        $data = [
            'course' => 'Web System Technologies!',
            'module' => 'Laravel Foundation',
        ];
        return view ('about', $data);
    }

    public function task($id) {
        $data = [
            'id' => $id,
            'title' => 'IT0049 - Block TW31',
        ];
        return view ('task', $data);
    }
}
