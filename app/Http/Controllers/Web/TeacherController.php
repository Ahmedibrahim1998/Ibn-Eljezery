<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Teacher;
use Illuminate\Contracts\View\View;

class TeacherController extends Controller
{
    public function show(Teacher $teacher): View
    {
        abort_unless($teacher->is_active, 404);

        $teacher->load(['courses' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order')]);

        return view('teachers.show', ['teacher' => $teacher]);
    }
}
